<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Providers\CloudflareApiServiceProvider;
use Ramsey\Uuid\Uuid;


class ProvisionController extends Controller
{
    public function doit(Request $request)
    {
        if ($request->session()->has('digitalocean') === false) {
            return redirect(url('/?loginToDigitalOceanFirstSilly'));
        }

        if (
            empty($request->input('size')) ||
            empty($request->input('distro')) ||
            empty($request->input('name')) ||
            empty($request->input('region'))
        ) {
            return redirect(url('/?invalidSizeOrDistroOrNameOrRegion'));
        }

        $name = $request->input('name');
        $size = $request->input('size');
        $distro = $request->input('distro');
        $region = $request->input('region');

        if (array_key_exists($size, config('digitalocean.sizes')) === false) { // Invalid size
            return redirect(url('/?sizeNotInConfigMustBeInvalidOrIAmOutOfDateLikeSausagesUsually'));
        }

        // For now do it the absolutely dreadful way
        // TODO: Tidy up with service provider/facade/something
        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);

        $uuid1 = Uuid::uuid1();

        $publicKey = (new \App\Fodor\Ssh\Keys())->getPublic($uuid1->toString());

        $droplet = $digitalocean->droplet();
        $key = $digitalocean->key();

        $keyCreated = $key->create('fodor-' . $uuid1, $publicKey); // TODO: Check result

        $created = $droplet->create($name . '-' . $uuid1, $region, $size, $distro, false, false, false, [$keyCreated->id]);

        if (empty($created)) {
           return redirect(url('/?createdDroplet=false'));
        }

        $dropletId = $created->id;
        // It doesn't have a network straight away - we need to wait for it to be created

        return redirect(url('/provision/waiting/' . $dropletId));
    }

    public function waiting(Request $request, $id)
    {
        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);
        $droplet = $digitalocean->droplet();

        $newDroplet = $droplet->getById($id);
        $status = $newDroplet->status;

        if ($status == "active") { // when you create it, it's 'new', then it goes active
            // Now we can get the IP, and create the subdomain
            // Then SSH in and provision
            foreach ($newDroplet->networks as $network) {
                //TODO: Support IPv6
                if ($network->version === 4) { // we only support ipv4 for this hacked together version
                    $ip = $network->ipAddress;
                    $client = new \Cloudflare\Api(env('CLOUDFLARE_API_EMAIL'), env('CLOUDFLARE_API_KEY'));
                    $subdomain = new \App\Fodor\Subdomain($client);

                    $result = $subdomain->create($subdomain->generateName(), $ip);

                    //TODO: Nice error message
                    if (empty($result)) {
                        die('Failed to create subdomain, but we created a droplet.  You shoud probably delete it, or setup your own subdomain, mmmkay? IP: ' . $ip);
                    }

                    return view('provision.complete', [
                        'domain' => $result->name,
                        'ip' => $ip
                    ]);
                }
            }
        }

        return view('provision.waiting', [
            'status' => $status
        ]);
    }

    public function start(Request $request)
    {
        if ($request->session()->has('digitalocean') === false) {
            return redirect(url('/?loginToDigitalOceanFirstSilly'));
        }

        $invalidFormat = false; // if it's not username/repo
        if (!$request->input('repo') || $invalidFormat) {
            return redirect(url('/?sorryItMessedUpSomehowWrongRepoFormat'));
        }

        $branch = 'master';
        list($username, $repo) = explode('/', $request->input('repo'));

        $client = new \Github\Client();
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);
        $fodorJson = $client->api('repo')->contents()->show($username, $repo, 'fodor.json', $branch); // TODO: fodor.json should be a config variable
        $fodorJson = base64_decode($fodorJson['content']);

        $fodorJson = json_decode($fodorJson, true);

        if (is_null($fodorJson) || $fodorJson === false) {
            return redirect(url('/?invalidFodorJsonFileSorryCouldNotDecode'));
        }

        if (empty($fodorJson['provisioner'])) {
            return redirect(url('/?noProvisionerSet'));
        }

        // Has to be less than 1mb
        $provisioner = $client->api('repo')->contents()->show($username, $repo, $fodorJson['provisioner'], $branch);

        if (empty($provisioner)) {
            return redirect(url('/?provisionerEmptyPartOne'));
        }

        $provisioner = base64_decode($provisioner['content']);

        if (empty($provisioner)) {
            return redirect(url('/?provisionerEmptyPartTwo'));
        }

        // We have a valid provisioner

        // TODO: Check provided size is valid
        $size = '512mb'; // TODO: Config variable for default size

        if (array_key_exists('required', $fodorJson['size']) === true) {
            $size = $fodorJson['size']['required'];
        } elseif (array_key_exists('suggested', $fodorJson['size']) === true) {
            $size = $fodorJson['size']['suggested'];
        }

        if (array_key_exists($size, config('digitalocean.sizes')) === false) { // Invalid size
            $size = '512mb'; // TODO: Config variable for default size
        }

        $distroInvalid = false;

        if (empty($fodorJson['distro']) || $distroInvalid) {
            $fodorJson['distro'] = 'ubuntu-14-04-x64';
        }

        return view('provision.start', [
            'repo' => $repo,
            'size' => $size,
            'distro' => $fodorJson['distro'],
        ]);
    }
}
