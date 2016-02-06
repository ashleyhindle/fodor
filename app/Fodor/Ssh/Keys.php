<?php

namespace app\Fodor\Ssh;

use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;

class Keys
{
    private $name;

    public function __construct($name=false)
    {
        $this->name = $name;
    }

    public function getPublicKeyPath()
    {
        return 'publickeys/' . $this->name . '.pub';
    }

    public function getPrivateKeyPath()
    {
        return 'privatekeys/' . $this->name . '.key';
    }

    public function getPublic()
    {
        $keys = $this->getPair();

        return Storage::get($keys['public']);
    }

    public function getPrivate()
    {
        $keys = $this->getPair();

        return Storage::get($keys['private']);
    }

    public function remove() {
        return Storage::disk('local')->delete(
            $this->getPublicKeyPath(),
            $this->getPrivateKeyPath()
        );
    }

    public function getPair()
    {
        $installationUuid = $this->name;
        $publicKeyName = $this->getPublicKeyPath();
        $privateKeyName = $this->getPrivateKeyPath();

        if (Storage::exists($publicKeyName) === false || Storage::exists($privateKeyName) === false) {
            $rsa = new RSA();
            $rsa->setPublicKeyFormat(RSA::PUBLIC_FORMAT_OPENSSH);
            $rsa->setComment('fodor@fodor.xyz');
            $keys = $rsa->createKey(2048);

            $privateKey = $keys['privatekey'];
            $publicKey = $keys['publickey'];

            Storage::disk('local')->put($privateKeyName, $privateKey);
            Storage::disk('local')->put($publicKeyName, $publicKey);
        }

        return [
            'public' => $publicKeyName,
            'private' => $privateKeyName,
        ];

        /*$sshConnection = ssh2_connect('mycloud.smellynose.com', 22, array('hostkey'=>'ssh-rsa'));
        $sshSuccess = ssh2_auth_pubkey_file(
            $sshConnection,
            'ahindle',
            storage_path('app/' . $publicKeyName),
            storage_path('app/' . $privateKeyName)
        );*/
    }
}