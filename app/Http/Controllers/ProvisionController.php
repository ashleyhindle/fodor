<?php namespace App\Http\Controllers;

use app\Exceptions\InvalidRepoException;
use App\Fodor\Config;
use App\Fodor\Github;
use App\Fodor\Input;
use App\Fodor\Repo;
use App\Provision;
use Illuminate\Http\Request;
use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

use App\Http\Requests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Ramsey\Uuid\Uuid;


class ProvisionController extends Controller
{

    private function getGithubClient()
    {
        $client = new \Github\Client();
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);

        return $client;
    }

    public function view(Request $request, $repo=false)
    {
        try {
            $repo = new Repo($repo);
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => $e->getMessage()]);
            return redirect('/?ohno');
        }

        $fullRepo = $repo->getName();

        $request->session()->set('intendedRepo', $repo->getName());

        $github = new Github($this->getGithubClient(), $repo);

        $json = $github->getFodorJson(); // TODO: Consider: should this be $repo->getFodorConfig() or getConfig or getFodorJson, and pass Github into Repo?

        if (empty($json)) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo or repo\'s fodor.json is non-existent']);
            return redirect(url('/'));
        }

        $fodorJson = new Config($json);

        try {
            $fodorJson->valid();
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => $e->getMessage()]);
            return redirect('/?ohno');
        }

        $fodorJsonUndecoded = $fodorJson->getJson(); // string of json

        // Has to be less than 1mb
        $provisioner = $github->getFileContents($fodorJson->provisioner);

        if (empty($provisioner)) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s provisioner was invalid or empty']);
            return redirect(url('/?ohno'));
        }

        $timeEstimate = 0;

        if(config('fodor.enable_time_estimates')) {
            $timeEstimate = \DB::select('select AVG(unix_timestamp(dateready)-unix_timestamp(datestarted)) as timeEstimate from provisions where repo=? and datestarted > ? and dateready is not null',
                [
                    $fullRepo,
                    (new \DateTime())->sub(new \DateInterval('P6M'))->format('Y-m-d H:i:s')
                ]
            );

            $timeEstimate = ($timeEstimate === null) ? 0 : floor($timeEstimate[0]->timeEstimate);
        }

        return view('provision.view', [
            'repo' => $fullRepo,
            'description' => $fodorJson->description,
            'imageUrl' => $this->getValidUrl($fodorJson, 'image'),
            'homepage' => $this->getValidUrl($fodorJson, 'homepage'),
            'fodorJson' => $fodorJsonUndecoded,
            'provisionerScript' => $provisioner,
            'timeEstimate' => $timeEstimate
        ]);
    }

    private function getValidUrl(Config $json, $key)
    {
        $urlProvided = (is_null($json->$key) === false);

        if ($urlProvided === false) {
            return '';
        }

        $url = $json->$key;

        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return '';
        }

        $parts = parse_url($url);

        if ($parts === false) { // Seriously messed up URL
            return '';
        }

        if (in_array($parts['scheme'], ['https']) === false) { // we only support https
            return '';
        }

        $parts['path'] = (isset($parts['path'])) ? $parts['path'] : '';

        // Don't add in user, pass, query params, or fragment
        return "{$parts['scheme']}://{$parts['host']}{$parts['path']}";
    }

    public function start(Request $request, $repo=false)
    {
        try {
            $repo = new Repo($repo);
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => $e->getMessage()]);
            return redirect('/?ohno');
        }

        $request->session()->set('intendedRepo', $repo);

        if ($request->session()->has('digitalocean') === false) {
            return redirect(url('/do/start'));
        }

        $request->session()->forget('intendedRepo');
        
        $provision = new Provision();
        $provision->repo = $repo->getName(); // Before it gets contaminated

        $github = new Github($this->getGithubClient(), $repo);

        $json = $github->getFodorJson();

        if (empty($json)) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo or repo\'s fodor.json is non-existent']);
            return redirect(url('/'));
        }

        $fodorJson = new Config($json);

        try {
            $fodorJson->valid();
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => $e->getMessage()]);
            return redirect('/?ohno');
        }

        $fodorJsonUndecoded = $fodorJson->getJson(); // string of json

        // Has to be less than 1mb
        $provisioner = $github->getFileContents($fodorJson->provisioner);

        if (empty($provisioner)) {
            $request->session()->flash(str_random(4), ['type' => 'warning', 'message' => 'This repo\'s provisioner was invalid or empty']);
            return redirect(url('/?ohno'));
        }

        // We have a valid provisioner

        $size = '512mb'; // TODO: Config variable for default size
        $suggestedSize = false;
        $requiredSize = false;

        if (array_key_exists('required', $fodorJson->size) === true) {
            $size = $fodorJson->size['required'];
            $requiredSize = $size;
        }

        // Suggested size overrides the default and required size

        if (array_key_exists('suggested', $fodorJson->size) === true) {
            $size = $fodorJson->size['suggested'];
            $suggestedSize = $size;
        }

        if (array_key_exists($size, config('digitalocean.sizes')) === false) { // Invalid size
            $size = '512mb'; // TODO: Config variable for default size
        }

        $isDistroInvalid = (!in_array($fodorJson->distro, config('digitalocean.distros')));

        if (empty($fodorJson->distro) || $isDistroInvalid) {
            $fodorJson->distro = 'ubuntu-14-04-x64';
        }

        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);

        $keysCached = false;
        $cacheKey = sha1($request->session()->get('digitalocean')['token'] . '-sshkeys');

        $keys = Cache::get($cacheKey, []);

        if (empty($keys)) {
            $keysFromDo = $digitalocean->key()->getAll();

            if (empty($keysFromDo)) {
                $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'You must have SSH keys attached to your DigitalOcean account to continue - https://cloud.digitalocean.com/settings/security']);
                return redirect(url('/provision/' . $provision->repo));
            }

            foreach ($keysFromDo as $key) {
                if (strpos($key->name, 'fodor-') !== 0) {
                    $keys[$key->id] = $key->name;
                }
            }

            if (empty($keys)) {
                $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'You must have SSH keys attached to your DigitalOcean account to continue - https://cloud.digitalocean.com/settings/security']);
                return redirect(url('/provision/' . $provision->repo));
            }

            Cache::put($cacheKey, $keys, 5); // Cache SSH keys for 5 minutes
        } else {
            $keysCached = true;
        }
        
        $requiredMemory = 0;
        if (!empty($requiredSize)) {
            $requiredMemory = (array_key_exists($requiredSize, config('digitalocean.sizes'))) ? config('digitalocean.sizes')[$requiredSize]['memory'] : 0;
        }

        $inputs = (is_null($fodorJson->inputs) === false) ? $fodorJson->inputs : [];
        array_walk($inputs, function(&$input) {
            $input['value'] = '';
            $input = new Input($input);
        });

        //get account email, and digitalocean_uuid
        //generate our own uuid
        //store in DB

        $provision->uuid = Uuid::uuid4()->toString();
        $provision->email = $request->session()->get('digitalocean')['email'];
        $provision->digitalocean_uuid = $request->session()->get('digitalocean')['uuid'];
        $provision->size = $size; // Default, can be overriden in next step
        $provision->distro = $fodorJson->distro;
        $provision->region = 'xxx'; // Default, can be overriden in next step
        $provision->datestarted = (new \DateTime('now', new \DateTimeZone('UTC')))->format('c');

        $validatingInputs = ($request->input('uuid') !== null) ? true : false;

        if ($validatingInputs) {
            $provision = \App\Provision::where('id', $request->input('provisionid'))->where('uuid', $request->input('uuid'))->first();

            /**
             * @var Input $input
             */
            $invalidInputs = [];
            foreach ($inputs as $input) {
                $value = $request->input('inputs')[$input->getName()];
                $input->value($value);
                $valid = $input->validate($value);
                if ($valid === false) {
                    $invalidInputs[] = ucwords($input->getName());
                }
            }

            $selectedKeys = $request->input('keys');
            $keysChosen = count($selectedKeys) > 0;

            if ($keysChosen === false) {
                $request->session()->now(str_random(4), ['type' => 'warning', 'message' => 'You must select an SSH key to add to the server']);
            } elseif (count($invalidInputs) === 0 && $keysChosen) {
                return $this->doit($request);
            } else {
                $request->session()->now(str_random(4), ['type' => 'warning', 'message' => 'Input value was invalid for ' . implode(', ', $invalidInputs)]);
            }
        } else {
            try {
                $saved = $provision->save();
            } catch (\Exception $e) {
                $saved = false;
            }

            if (empty($saved)) { // Failed to save
                $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Failed to save the provision data to the database, please destroy your droplet']);
                return redirect(url('/provision/' . $provision->repo));
            }
        }

        return view('provision.start', [
            'repo' => $repo->getRepoName(),
            'size' => [
                'default' => $size,
                'suggested' => $suggestedSize,
                'required' => $requiredSize
            ],
            'requiredMemory' => $requiredMemory,
            'description' => $fodorJson->description,
            'distro' => $fodorJson->distro,
            'keys' => $keys,
            'provisionid' => $provision->id,
            'provision' => $provision,
            'id' => $provision->id,
            'uuid' => $provision->uuid,
            'inputs' => $inputs,
            'keysCached' => $keysCached
        ]);
    }

    public function doit(Request $request)
    {
        if ($request->session()->has('digitalocean') === false) {
            return redirect(url('/?loginToDigitalOceanFirstSilly'));
        }

        $errors = [];

        if (empty($request->input('size'))) {
            $errors['size'] = true;
        }

        if (empty($request->input('distro'))) {
            $errors['distro'] = true;
        }

        if (empty($request->input('name'))) {
            $errors['name'] = true;
        }

        if(empty($request->input('id'))) {
            $errors['id'] = true;
        }

        if (!empty($errors) ||
            empty($request->input('uuid')) ||
            empty($request->input('repo')) ||
            empty($request->input('region'))
        ) {
            return redirect(url('/?invalidSizeOrDistroOrNameOrRegion2'));
        }

        $inputs = $request->input('inputs', []);
        $id = $request->input('id');
        $uuid = $request->input('uuid');

        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first(); // TODO: Check they own it
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return redirect('/?ohno');
        }

        $provisionid = $id;
        $name = $request->input('name');
        $repo = $request->input('repo');
        $size = $request->input('size');
        $distro = $request->input('distro');
        $region = $request->input('region');
        $keys = $request->input('keys', []);
        $keys = array_keys($keys);

        $request->session()->set("inputs.{$uuid}", $inputs);

        if (array_key_exists($size, config('digitalocean.sizes')) === false) { // Invalid size
            return redirect(url('/?sizeNotInConfigMustBeInvalidOrIAmOutOfDateLikeSausagesUsually'));
        }

        if (array_key_exists($region, config('digitalocean.regions')) === false) { // Invalid region
            return redirect(url('/?sizeNotInConfigMustBeInvalidOrIAmOutOfDateLikeSausagesUsuallyREGION'));
        }

        if (in_array($distro, config('digitalocean.distros')) === false) { // Invalid region
            return redirect(url('/?distro invalid'));
        }

        // For now do it the absolutely dreadful way
        // TODO: Tidy up with service provider/facade/something
        // If we did have a users table it could be stored in there
        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);

        $publicKey = (new \App\Fodor\Ssh\Keys($provision->uuid))->getPublic();

        $droplet = $digitalocean->droplet();
        $key = $digitalocean->key();

        try {
            $keyCreated = $key->create('fodor-' . $provision->uuid, $publicKey); // TODO: Check result
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not add SSH key to your DigitalOcean account: ' . $e->getMessage()]);
            return redirect('/provision/start/' . $repo);
        }

        $keys[] = $keyCreated->id;

        try {
            $hostname = 'fodor-' . $name . '-' . $provision->uuid;
            $created = $droplet->create($hostname, $region, $size, $distro, false, false, false, $keys);
        } catch (\Exception $e) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not create DigitalOcean droplet: ' . $e->getMessage()]);
            return redirect('/provision/start/' . $repo);
        }

        if (empty($created)) {
           return redirect(url('/?createdDroplet=false'));
        }

        $dropletId = $created->id;

        $provision->region = $region;
        $provision->size = $size;
        $provision->dropletid = $dropletId;
        $provision->save();
        // It doesn't have a network straight away - we need to wait for it to be created

        return redirect(url('/provision/waiting/' . $provisionid . '/' . $provision->uuid));
    }

    public function waitingJson(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first(); // TODO: Check they own it

        if ($provision === null) {
            return response()->json(['status' => 'broken']);
        }

        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);
        $droplet = $digitalocean->droplet();

        $status = $droplet->getById($provision->dropletid)->status;

        return response()->json(['status' => $status]);
    }

    public function waiting(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first(); // TODO: Check they own it
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return redirect('/?ohno');
        }


        $adapter = new GuzzleHttpAdapter($request->session()->get('digitalocean')['token']);
        $digitalocean = new DigitalOceanV2($adapter);
        $droplet = $digitalocean->droplet();

        $newDroplet = $droplet->getById($provision->dropletid);
        $status = $newDroplet->status;

        if ($status == "active") { // when you create it, it's 'new', then it goes active
            // Now we can get the IP, and create the subdomain
            // Then SSH in and provision

            foreach ($newDroplet->networks as $network) {
                //TODO: Support IPv6, though causes issues with digitalocean apt-get timing out currently
                if ($network->version === 4) { // we only support ipv4 for this hacked together version
                    $ip = $network->ipAddress;

                    $client = new \Cloudflare\Api(env('CLOUDFLARE_API_EMAIL'), env('CLOUDFLARE_API_KEY'));
                    $dns = new \Cloudflare\Zone\Dns($client);
                    $subdomain = new \App\Fodor\Subdomain($dns);
                    $subdomainName = $subdomain->generateName($provision->id);
                    $result = $subdomain->create($subdomainName, $ip);

                    if (empty($result)) {
                        $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'We failed to create the DNS needed for ' . $ip . ', really sorry about that.  You should probably delete this failed attemp :(']);
                        return redirect('/?ohno');
                    }

                    $provision->status = 'active'; // TODO: Provision class should handle this, and use constants - or just $provision->setActive() or $provision->active(true);
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
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first();
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return redirect('/?ohno');
        }

        $redisKey = config('rediskeys.digitalocean_token') . $uuid;
        Redis::set($redisKey, $request->session()->get('digitalocean')['token']);

        // This expire is a backup in case the provision job fails, and doesn't delete the key explicitly at the end
        Redis::expire($redisKey, (60 * 60)); // 60 minutes - if a provision job takes longer than this we won't be able to delete our SSH keys from the users DO account

        $provision->inputs = $request->session()->get("inputs.{$uuid}");

        // It doesn't accept SSH connections immediately after creation, so we delay
        $job = (new \App\Jobs\Provision($provision))->delay(5);
        $this->dispatch($job);

        return redirect(url('/provision/provisioning/' . $provision->id . '/' . $provision->uuid));
    }

    public function provisioning(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first(); // TODO: Check ownership
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return redirect('/?ohno');
        }

        $request->session()->set('log-' . $uuid, 0);

        $replacements = [
            '{{DOMAIN}}' => $provision->domain()
        ];

        $repo = new Repo($provision->repo);
        $github = new Github($this->getGithubClient(), $repo);

        $json = $github->getFodorJson();
        $fodorJson = new Config($json);

        return view('provision.provisioning', [
            'id' => $id,
            'uuid' => $uuid,
            'provision' => $provision,
            'failedText' => $fodorJson->getText('failed', $replacements)
        ]);
    }

    public function ready(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first(); // TODO: Check ownership
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return redirect('/?ohno');
        }

        $repo = new Repo($provision->repo);
        $github = new Github($this->getGithubClient(), $repo);

        $fodorJson = new Config($github->getFodorJson());

        $links = [];

        if (!is_null($fodorJson->links)) {
            foreach($fodorJson->links as $link) {
                $links[] = [
                    'title' => $link['title'],
                    'url' => str_replace('{{DOMAIN}}', $provision->subdomain . '.fodor.xyz', $link['url'])
                ];
            }
        }

        $replacements = [
            '{{DOMAIN}}' => $provision->domain()
        ];

        return view('provision.complete', [
            'links' => $links,
            'domain' => $provision->subdomain . '.fodor.xyz',
            'ip' => $provision->ipv4,
            'provision' => $provision,
            'successText' => $fodorJson->getText('complete', $replacements)
        ]);
    }

    public function log(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first();
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return response()->json(['status' => 'broken']);
        }

        $logPath = storage_path('logs/provision/' . $uuid . '.output');

        // We have finished provisioning and we've sent the full log
        if ($provision->status == 'errored' && $request->session()->get('log-' . $uuid) == filesize($logPath)) {
            return response()->json(['status' => 'errored']);
        }

        if ($provision->status == 'ready') { // We have finished provisioning
            return response()->json(['status' => 'ready']);
        }

        // Storage::exists checks if it's a real file with 'is_file' which fails on vagrant for some reason
        // So we have to do it old style
        if (file_exists(storage_path('logs/provision/' . $uuid . '.output')) === false) {
            return response()->json(['error' => 'FILE_NONEXISTENT']);
        }

        $lines = [];
        $fp = fopen($logPath, 'r');
        fseek($fp, $request->session()->get('log-' . $uuid));
        while (($line = fgets($fp, 4096)) !== false) {
            $result = preg_match('/^\[[0-9]{4}\-[0-9]{2}\-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}\] OUTPUT.INFO: (.+) \[\] \[\]$/', $line, $match);
            if ($result === 1) {
                $lines[] = $match[1];
            }
        }

        $request->session()->set('log-' . $uuid, filesize($logPath));
        return response()->json(['lines' => $lines]);
    }

    public function logDownload(Request $request, $id, $uuid)
    {
        $provision = \App\Provision::where('id', $id)->where('uuid', $uuid)->first();
        if ($provision === null) {
            $request->session()->flash(str_random(4), ['type' => 'danger', 'message' => 'Could not find id/uuid combo']);
            return response()->json(['status' => 'broken']);
        }

        $logPath = storage_path('logs/provision/' . $uuid . '.output');

        // Storage::exists checks if it's a real file with 'is_file' which fails on vagrant for some reason
        // So we have to do it old style
        if (file_exists(storage_path('logs/provision/' . $uuid . '.output')) === false) {
            return App::abort(404);
        }

        return response()->download($logPath, 'fodor-provisioning-log-' . $uuid . '.log');
    }
}
