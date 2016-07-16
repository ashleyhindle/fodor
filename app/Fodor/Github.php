<?php namespace App\Fodor;

use Github\Client;
use Illuminate\Support\Facades\Cache;

class Github
{
    private $client;
    private $repo;

    public function __construct(Client $client, Repo $repo)
    {
        $this->repo = $repo;
        $this->client = $client;
    }

    public function getFile($filename)
    {
        $cacheKey = sha1($filename . $this->repo->getUsername() . $this->repo->getRepoName() . $this->repo->getBranch());

        $value = Cache::get($cacheKey);

        if (is_null($value) === false) {
            return $value;
        }

        try {
            $file = $this->client->api('repo')->contents()->show($this->repo->getUsername(), $this->repo->getRepoName(), $filename, $this->repo->getBranch());
            Cache::put($cacheKey, $file, 2); // File is an array
        } catch (\Exception $e) {
            return false;
        }

        return $file;
    }

    public function getFileContents($filename)
    {
        $file = $this->getFile($filename);

        if ($file === false) {
            return false;
        }

        return base64_decode($file['content']);
    }

    public function getFodorJson()
    {
        return $this->getFileContents('fodor.json');
    }
}