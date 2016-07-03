<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use DigitalOceanV2\Adapter\GuzzleHttpAdapter;
use DigitalOceanV2\DigitalOceanV2;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

ini_set("auto_detect_line_endings", true);

class Provision extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $provision;
    private $inputs;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Provision $provision)
    {
        $this->provision = $provision;
        $this->inputs = $this->provision->inputs;
    }


    public function packet_handler($str)
    {
        echo $str;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $log = new Logger('LOG');
        $log->pushHandler(new StreamHandler(storage_path('logs/provision/' . $this->provision->uuid . '.log'), Logger::INFO));

        $logProvisionerOutput = new Logger('OUTPUT');
        $logProvisionerOutput->pushHandler(new StreamHandler(storage_path('logs/provision/' . $this->provision->uuid . '.output'), Logger::INFO));

        $log->addInfo("Provisioning started");

        $uuid = $this->provision->uuid;
        $sshKeys = new \App\Fodor\Ssh\Keys($uuid);

        $adapter = new GuzzleHttpAdapter($this->provision->digitalocean_token);
        $digitalocean = new DigitalOceanV2($adapter);


        //TODO: This should be a beanstalk job with AJAX updating
        //DO ALL THE PROVISIONING HERE - GET GITHUB FODOR.JSON, SSH IN, DO IT ALL,
        $this->provision->status = 'provision';
        $this->provision->save();
        $log->addInfo("Set status to provision");

        $branch = 'master';
        list($username, $repo) = explode('/', $this->provision->repo);

        $client = new \Github\Client();
        $client->authenticate(env('GITHUB_API_TOKEN'), false, \Github\Client::AUTH_HTTP_TOKEN);
        $fodorJson = $client->api('repo')->contents()->show($username, $repo, 'fodor.json', $branch); // TODO: fodor.json and branch should be a config variable
        $log->addInfo("Fetched fodor.json from GitHub: {$fodorJson['content']}");

        $fodorJson = base64_decode($fodorJson['content']);
        $fodorJson = json_decode($fodorJson, true);

        var_dump($this->inputs);

        $baseScript = \View::make('provision-base.ubuntu-14-04-x64',[
            'installpath' => $fodorJson['installpath'],
            'name' => $this->provision->repo,
	        'rootPasswordEscaped' => addslashes($this->provision->rootPassword),
            'domain' => $this->provision->subdomain . '.fodor.xyz',
            'ipv4' => $this->provision->ipv4,
            'inputs' => $this->inputs
        ])->render();

        echo $baseScript;

        $provisionerScript = $client->api('repo')->contents()->show($username, $repo, $fodorJson['provisioner'], $branch);
        $log->addInfo("Fetched provisioner script from GitHub: {$provisionerScript['content']}");

        $providedScript = base64_decode($provisionerScript['content']);
        $remoteProvisionScriptPath = '/tmp/fodor-provision-script-' . $this->provision->uuid;

        if($ssh = ssh2_connect($this->provision->ipv4, 22)) {
            $log->addInfo("Successfully connected to the server via SSH: {$this->provision->ipv4}");

            if(ssh2_auth_pubkey_file($ssh, 'root', storage_path('app/' . $sshKeys->getPublicKeyPath()), storage_path('app/' . $sshKeys->getPrivateKeyPath()))) {
                $log->addInfo("Successfully authenticated");
                $log->addInfo("Running: /bin/bash '{$remoteProvisionScriptPath}'");

                $sftp = ssh2_sftp($ssh); //TODO error check and refactor all of the code we've written so far
                $stream = fopen("ssh2.sftp://{$sftp}{$remoteProvisionScriptPath}", 'w');
                fwrite($stream, $baseScript . PHP_EOL . $providedScript);
                fclose($stream);
                $log->addInfo("Transferred provisioner-combined script");

                // TODO: Investigate setting environment variables here instead of with export
                $stream = ssh2_exec($ssh, '(/bin/bash ' . escapeshellarg($remoteProvisionScriptPath) . ' 2>&1); echo -e "\n$?"');
                stream_set_blocking($stream, true);
                $lastString = '';
                while(($string = fgets($stream)) !== false) {
                    $logProvisionerOutput->addInfo($string);
                    echo $string;
                    $lastString = $string;
                }

                $log->addInfo('EXIT CODE: ' . $lastString);
                $exitCode = (int) trim($lastString);

                fclose($stream);
            } else {
                $log->addError("Failed to authenticate to SSH");
                exit(1);
            }
        } else {
            $log->addError("Failed to connect to SSH");
            exit(1);
        }

        // ## REMOVE SSH KEYS FROM DIGITALOCEAN AND OUR LOCAL FILESYSTEM ##

        $keysFromDo = $digitalocean->key()->getAll();

        if (!empty($keysFromDo)) {
            foreach ($keysFromDo as $key) {
                if ($key->name == 'fodor-' . $this->provision->uuid) {
                    $digitalocean->key()->delete($key->id); // Remove our fodor SSH key from the users DigitalOcean account
                    $log->addInfo("Removed SSH key: {$key->name}: {$key->id} from DigitalOcean");
                }
            }
        }

        if (\Storage::exists($sshKeys->getPublicKeyPath())) {
            try {
                $log->addInfo("Removed local SSH Keys");
                $sshKeys->remove(); // uuid is the name of the file
            } catch (Exception $e) {
                // TODO: Handle.  We should probably be alerted as we don't want these lying around
            }
        }

        $this->provision->digitalocean_token = ''; // We don't want to store tokens that can be used for nefarious purposes
        $this->provision->dateready = (new \DateTime('now', new \DateTimeZone('UTC')))->format('c');
        $this->provision->exitcode = $exitCode;
        $this->provision->status = ($exitCode === 0) ? 'ready' : 'errored'; //TODO: Other distros or shells?
        $this->provision->save();

        //TODO: If it errored, an alert should be sent out for investigation

        $log->addInfo("Set provision row's status to {$this->provision->status}, we're done here");
    }
}
