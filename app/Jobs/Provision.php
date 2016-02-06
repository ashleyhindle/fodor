<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

use phpseclib\Net\SSH2;
use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

class Provision extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $provision;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Provision $provision)
    {
        $this->provision = $provision;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $uuid = $this->provision->uuid;
        $sshKeys = new \App\Fodor\Ssh\Keys($uuid);

        $adapter = new GuzzleHttpAdapter($this->provision->digitalocean_token);
        $digitalocean = new DigitalOceanV2($adapter);


        //TODO: This should be a beanstalk job with AJAX updating
        //DO ALL THE PROVISIONING HERE - GET GITHUB FODOR.JSON, SSH IN, DO IT ALL,
        $this->provision->status = 'provision';
        $this->provision->save();

        $branch = 'master';
        list($username, $repo) = explode('/', $this->provision->repo);

        $client = new \Github\Client();
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);
        $fodorJson = $client->api('repo')->contents()->show($username, $repo, 'fodor.json', $branch); // TODO: fodor.json and branch should be a config variable
        $fodorJson = base64_decode($fodorJson['content']);
        $fodorJson = json_decode($fodorJson, true);

        $bashScript = \View::make('provision-base.ubuntu-14-04-x64',[
            'installpath' => $fodorJson['installpath'],
            'name' => $this->provision->repo,
        ])->render();

        $provisionerScript = $client->api('repo')->contents()->show($username, $repo, $fodorJson['provisioner'], $branch);
        $bashScript .= base64_decode($provisionerScript['content']);

        $key = new RSA();
        $key->loadKey((new \App\Fodor\Ssh\Keys($this->provision->uuid))->getPrivate());

        $sftp = new SFTP($this->provision->ipv4);
        if (!$sftp->login('root', $key)) {
            exit('SFTP Login Failed');
        }

        $remoteProvisionScriptPath = '/tmp/fodor-provision-script-' . $this->provision->uuid;
        $sftp->put($remoteProvisionScriptPath, $bashScript);

        $ssh = new SSH2($this->provision->ipv4);
        if (!$ssh->login('root', $key)) {
            exit('Login Failed');
        }

        echo "Successfully connected to the server via SSH" . PHP_EOL;
        echo "Running: /bin/bash '{$remoteProvisionScriptPath}'" . PHP_EOL;

        $ssh->enablePTY();
        $ssh->exec("/bin/bash '{$remoteProvisionScriptPath}'");
        $ssh->setTimeout(3600); // Max execution time for the provisioning script
        echo $ssh->read();

        // ## REMOVE SSH KEYS FROM DIGITALOCEAN AND OUR LOCAL FILESYSTEM ##

        $keysFromDo = $digitalocean->key()->getAll();

        if (!empty($keysFromDo)) {
            foreach ($keysFromDo as $key) {
                if (strpos($key->name, 'fodor-') === 0) {
                    // TODO: Only remove the ones for this Droplet, based on uuid no doubt
                    $digitalocean->key()->delete($key->id); // Remove our fodor key(s) - this removes them all though so if they're provisioning two at once it could mess it up
                    echo "Removed SSH key: {$key->name}: {$key->id} from DigitalOcean" . PHP_EOL;
                }
            }
        }

        if (\Storage::exists($sshKeys->getPublicKeyPath())) {
            try {
                echo "Removed local SSH Keys" . PHP_EOL;
                $sshKeys->remove(); // uuid is the name of the file
            } catch (Exception $e) {
                // TODO: Handle.  We should probably be alerted as we don't want these lying around
            }
        }

        $this->provision->dateready = (new \DateTime('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        $this->provision->status = 'ready';
        $this->provision->save();

        echo "Set provision row to ready, we're done here" . PHP_EOL;
    }
}
