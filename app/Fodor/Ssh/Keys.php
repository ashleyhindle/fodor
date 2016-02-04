<?php

namespace app\Fodor\Ssh;

use Illuminate\Support\Facades\Storage;
use phpseclib\Crypt\RSA;

class Keys
{
    public function getPublic($name=false)
    {
        $keys = $this->getPair($name);

        return Storage::get($keys['public']);
    }

    public function getPair($name=false)
    {
        if (empty($name)) {
            return false;
        }

        $installationUuid = $name;
        $publicKeyName = 'publickeys/' . $installationUuid . '.pub';
        $privateKeyName = 'privatekeys/' . $installationUuid . '.key';

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