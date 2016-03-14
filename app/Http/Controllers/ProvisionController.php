<?php

namespace App\Http\Controllers;

use App\Provision;
use Illuminate\Http\Request;
use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Providers\CloudflareApiServiceProvider;
use Mockery\CountValidator\Exception;
use Ramsey\Uuid\Uuid;


class ProvisionController extends Controller
{

    public function view(Request $request, $repo=false)
    {
        if (empty($repo)) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'No repo provided']);
            return redirect('/?ohno');
        }

        $fullRepo = $repo;

        $invalidFormat = (strpos($repo, '/') === false);

        if (empty($repo) || $invalidFormat) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'The repo name "' . $repo . '" is invalid']);
            return redirect(url('/'));
        }

        $request->session()->set('intendedRepo', $repo);

        $branch = 'master';
        list($username, $repo) = explode('/', $repo);

        $client = new \Github\Client();
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);
        try {
            $fodorJson = $client->api('repo')->contents()->show($username, $repo, 'fodor.json', $branch); // TODO: fodor.json should be a config variable
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo or repo\'s fodor.json is non-existent']);
            return redirect(url('/'));
        }

        $fodorJson = base64_decode($fodorJson['content']);
        $fodorJsonUndecoded = $fodorJson;

        $fodorJson = json_decode($fodorJson, true);

        if (is_null($fodorJson) || $fodorJson === false) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s fodor.json is invalid']);
            return redirect(url('/'));
        }

        if (empty($fodorJson['provisioner'])) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s fodor.json doesn\'t provide a provisioner']);
            return redirect(url('/'));
        }

        if (empty($fodorJson['description'])) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s fodor.json doesn\'t provide a description']);
            return redirect(url('/'));
        }

        // Has to be less than 1mb
        try {
            $provisioner = $client->api('repo')->contents()->show($username, $repo, $fodorJson['provisioner'], $branch);
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s provisioner was invalid or empty']);
            return redirect(url('/'));
        }

        if (empty($provisioner)) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s provisioner was invalid or empty']);
            return redirect(url('/'));
        }

        $provisioner = base64_decode($provisioner['content']);

        if (empty($provisioner)) {
            $request->session()->flash(str_random(4), 'This repo\'s provisioner was in the wrong format or too large');
            return redirect(url('/'));
        }

        return view('provision.view', [
            'repo' => $fullRepo,
            'description' => $fodorJson['description'],
            'fodorJson' => $fodorJsonUndecoded,
            'provisionerScript' => $provisioner
        ]);
    }

    public function start(Request $request, $repo=false)
    {
        if (empty($repo)) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'No repo provided']);
            return redirect('/?ohno');
        }

        $invalidFormat = (strpos($repo, '/') === false);
        if (empty($repo) || $invalidFormat) {
            $request->session()->flash('status', ['type' => 'warning', 'message' => 'This repo is invalid']);
            return redirect(url('/'));
        }

        $request->session()->set('intendedRepo', $repo);

        if ($request->session()->has('digitalocean') === false) {
            return redirect(url('/do/start'));
        }

        $request->session()->forget('intendedRepo');

        $provision = new Provision();
        $provision->repo = $repo; // Before it gets contaminated

        $branch = 'master';
        list($username, $repo) = explode('/', $repo);

        $client = new \Github\Client();
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);

        try {
            $fodorJson = $client->api('repo')->contents()->show($username, $repo, 'fodor.json', $branch); // TODO: fodor.json should be a config variable
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo or repo\'s fodor.json is non-existent']);
            return redirect(url('/'));
        }

        $fodorJson = base64_decode($fodorJson['content']);
        $fodorJson = json_decode($fodorJson, true);


        if (is_null($fodorJson) || $fodorJson === false) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s fodor.json is invalid']);
            return redirect(url('/'));
        }

        if (empty($fodorJson['provisioner'])) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s fodor.json doesn\'t provide a provisioner']);
            return redirect(url('/'));
        }

        if (empty($fodorJson['description'])) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s fodor.json doesn\'t provide a description']);
            return redirect(url('/'));
        }


        // Has to be less than 1mb
        try {
            $provisioner = $client->api('repo')->contents()->show($username, $repo, $fodorJson['provisioner'], $branch);
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s provisioner was invalid or empty']);
            return redirect(url('/'));
        }

        if (empty($provisioner)) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s provisioner was invalid or empty']);
            return redirect(url('/'));
        }

        $provisioner = base64_decode($provisioner['content']);

        if (empty($provisioner)) {
            $request->session()->flash(str_random(4), 'This repo\'s provisioner was in the wrong format or too large');
            return redirect(url('/'));
        }

        // We have a valid provisioner

        // TODO: Check provided size is valid
        $size = '512mb'; // TODO: Config variable for default size
        $suggestedSize = false;
        $requiredSize = false;

        if (array_key_exists('required', $fodorJson['size']) === true) {
            $size = $fodorJson['size']['required'];
            $requiredSize = $size;
        }

        // Suggested size overrides the default and required size

        if (array_key_exists('suggested', $fodorJson['size']) === true) {
            $size = $fodorJson['size']['suggested'];
            $suggestedSize = $size;
        }

        if (array_key_exists($size, config('digitalocean.sizes')) === false) { // Invalid size
            $size = '512mb'; // TODO: Config variable for default size
        }

        $distroInvalid = false;

        if (empty($fodorJson['distro']) || $distroInvalid) {
            $fodorJson['distro'] = 'ubuntu-14-04-x64';
        }

        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);

        $keysFromDo = $digitalocean->key()->getAll();
        $keys = [];

        if (!empty($keysFromDo)) {
            foreach ($keysFromDo as $key) {
                if (strpos($key->name, 'fodor-') !== 0) {
                    $keys[$key->id] = $key->name;
                }
            }
        }

        $account = $digitalocean->account()->getUserInformation();

        //get account email, and digitalocean_uuid
        //generate our own uuid
        //store in DB

        $provision->uuid = Uuid::uuid4()->toString();
        $provision->email = $account->email;
        $provision->digitalocean_uuid = $account->uuid;
        $provision->size = $size; // Default, can be overriden in next step
        $provision->distro = $fodorJson['distro'];
        $provision->region = 'xxx'; // Default, can be overriden in next step
        $provision->datestarted = (new \DateTime('now', new \DateTimeZone('UTC')))->format('c');

        try {
            $saved = $provision->save();
        } catch(Exception $e) {
            $saved = false;
        }

        if (empty($saved)) { // Failed to save
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Failed to save the provision data to the database, please destroy your droplet']);
            return redirect(url('/provision/'.$provision->repo));
        }

        $requiredMemory = 0;
        if (!empty($requiredSize)) {
            $requiredMemory = (array_key_exists($requiredSize, config('digitalocean.sizes'))) ? config('digitalocean.sizes')[$requiredSize]['memory'] : 0;
        }

        return view('provision.start', [
            'repo' => $repo,
            'size' => [
                'default' => $size,
                'suggested' => $suggestedSize,
                'required' => $requiredSize
            ],
            'requiredMemory' => $requiredMemory,
            'description' => $fodorJson['description'],
            'distro' => $fodorJson['distro'],
            'keys' => $keys,
            'provisionid' => $provision->id
        ]);
    }

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

        $provisionid = $request->input('provisionid');
        $provision = \App\Provision::find($provisionid); // TODO: Check they own it
        
        $name = $request->input('name');
        $size = $request->input('size');
        $distro = $request->input('distro');
        $region = $request->input('region');
        $keys = $request->input('keys', []);

        $keys = array_keys($keys);

        if (array_key_exists($size, config('digitalocean.sizes')) === false) { // Invalid size
            return redirect(url('/?sizeNotInConfigMustBeInvalidOrIAmOutOfDateLikeSausagesUsually'));
        }

        if (array_key_exists($region, config('digitalocean.regions')) === false) { // Invalid region
            return redirect(url('/?sizeNotInConfigMustBeInvalidOrIAmOutOfDateLikeSausagesUsuallyREGION'));
        }

        // TODO: If there aren't any SSH keys provided, other than ours, we need to set a root password
        // DigitalOcean won't send a root password as we added fodor's ssh key

        // For now do it the absolutely dreadful way
        // TODO: Tidy up with service provider/facade/something
        // If we did have a users table it could be stored in there
        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);

        $publicKey = (new \App\Fodor\Ssh\Keys($provision->uuid))->getPublic();

        $droplet = $digitalocean->droplet();
        $key = $digitalocean->key();

        $keyCreated = $key->create('fodor-' . $provision->uuid, $publicKey); // TODO: Check result
        $keys[] = $keyCreated->id;

        // TODO: Multi distro support
        $rootPassword = str_random(32); // TODO: Should we delete all rootPasswords every X hours for old (1hour?) droplets?
        $rootPasswordEscaped = addslashes($rootPassword);

        $created = $droplet->create('fodor-' . $name . '-' . $provision->uuid, $region, $size, $distro, false, false, false, $keys);

        if (empty($created)) {
           return redirect(url('/?createdDroplet=false'));
        }

        $dropletId = $created->id;

        $provision->rootPassword = $rootPassword;
        $provision->region = $region;
        $provision->size = $size;
        $provision->dropletid = $dropletId;
        $provision->save();
        // It doesn't have a network straight away - we need to wait for it to be created

        return redirect(url('/provision/waiting/' . $provisionid . '/' . $provision->uuid));
    }

    public function waitingJson(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::find($id); // TODO: Check they own it

        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);
        $droplet = $digitalocean->droplet();

        $status = $droplet->getById($provision->dropletid)->status;

        return response()->json(['status' => $status]);
    }

    public function waiting(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::find($id); // TODO: Check they own it
        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);
        $droplet = $digitalocean->droplet();

        $newDroplet = $droplet->getById($provision->dropletid);
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
                    $subdomainName = $subdomain->generateName();
                    $result = $subdomain->create($subdomainName, $ip);

                    //TODO: Nice error message
                    if (empty($result)) {
                        die('Failed to create subdomain, but we created a droplet.  You shoud probably delete it, or setup your own subdomain, mmmkay? IP: ' . $ip);
                    }

                    $provision->status = 'active'; // TODO: Provision class should handle this, and use constants
                    $provision->ipv4 = $ip;
                    $provision->subdomain = $subdomainName;
                    $provision->save();

                    return redirect(url('/provision/provision/' . $provision->id . '/' . $provision->uuid));
                }
            }
        }

        return view('provision.waiting', [
            'status' => $status,
            'id' => $id,
            'uuid' => $uuid,
            'provision' => $provision
        ]);
    }

    public function provision(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::find($id);
        // We add it here so it's not in the database for a long time.  Though potentially if this is secure enough (maybe we should encrypt it)
        //  then we can use this in future for allowing people to manage the Fodor droplets from Fodor? Delete/update TODO/CONSIDER

        $provision->digitalocean_token = $request->session()->get('digitalocean')['token'];
        $provision->save();

        $job = (new \App\Jobs\Provision($provision))->delay(1); // It doesn't accept SSH connections for a bit after being available
        $this->dispatch($job);

        return redirect(url('/provision/provisioning/' . $provision->id . '/' . $provision->uuid));
    }

    public function provisioning(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::find($id); // TODO: Check ownership
        $request->session()->set('log-' . $uuid, 0);

        return view('provision.provisioning', [
            'id' => $id,
            'uuid' => $uuid,
            'provision' => $provision
        ]);
    }

    public function ready(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::find($id); // TODO: Check ownership
        $provisionCloned = clone $provision;

        $provision->rootPassword = ''; // Delete root password, so if we get hacked we don't give out access to people's servers
        $provision->save();

        $branch = 'master';
        list($username, $repo) = explode('/', $provision->repo);

        $client = new \Github\Client(); // TODO: DRY
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);
        $fodorJson = $client->api('repo')->contents()->show($username, $repo, 'fodor.json', $branch); // TODO: fodor.json and branch should be a config variable
        $fodorJson = base64_decode($fodorJson['content']);
        $fodorJson = json_decode($fodorJson, true);

        $links = [];

        if (array_key_exists('links', $fodorJson)) {
            foreach($fodorJson['links'] as $link) {
                $links[] = [
                    'title' => $link['title'],
                    'url' => str_replace('{{DOMAIN}}', $provision->subdomain . '.fodor.xyz', $link['url'])
                ];
            }
        }

        return view('provision.complete', [
            'links' => $links,
            'domain' => $provisionCloned->subdomain . '.fodor.xyz',
            'ip' => $provisionCloned->ipv4,
            'provision' => $provisionCloned
        ]);
    }

    public function log(Request $request, $id, $uuid) // TODO: Check user owns, all throughout this class
    {
        $provision = \App\Provision::find($id); // TODO: Check ownership

        if ($provision->status == 'ready') { // We have finished provisioning
            return response()->json(['status' => 'ready']);
        }

        $logPath = storage_path('logs/provision/' . $uuid . '.output');

        // Storage::exists checks if it's a real file with 'is_file' which fails on vagrant for some reason
        // So we have to do it old style
        if (file_exists(storage_path('logs/provision/' . $uuid . '.output')) === false) {
            return response()->json(['error' => 'FILE_NONEXISTENT'.storage_path('logs/provision/' . $uuid . '.output')]);
        }

        $lines = [];
        $fp = fopen($logPath, 'r');
        fseek($fp, $request->session()->get('log-' . $uuid));
        while (($line = fgets($fp, 4096)) !== false) {
            preg_match('/^\[[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\] OUTPUT.INFO: (.+)$/', $line, $match);
            $lines[] = $match[1];
        }

        $request->session()->set('log-' . $uuid, filesize($logPath));
        return response()->json(['lines' => $lines]);
    }
}
