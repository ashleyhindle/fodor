<?php namespace App\Fodor;

use App\Exceptions\InvalidRepoException;

class Repo
{
    private $baseUrl = 'https://github.com/'; // TODO: Support gitlab and private hosts
    private $name;
    private $fullUrl;
    private $username;
    private $repoName; // repo is provided as ashleyhindle/fodor; ashleyhindle=username, fodor=repoName
    private $branch = 'master';

    public function __construct($name)
    {
        $this->setName($name);
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getRepoName()
    {
        return $this->repoName;
    }

    public function getBranch()
    {
        return $this->branch;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        $valid = $this->valid(); // Exception will bubble

        if ($valid === false) {
            Throw new InvalidRepoException('Invalid repo'); // Shouldn't get here, but a good backup
        }

        list($username, $repoName) = explode('/', $this->name);
        $this->username = $username;
        $this->repoName = $repoName;
        $this->fullUrl  = $this->baseUrl . $this->name;

        return $this;
    }

    public function valid()
    {
        $valid = true;

        $name = $this->name;

        if (empty($name)) {
            Throw new InvalidRepoException('Repo name is empty');
        }

        $invalidFormat = (strpos($name, '/') === false);

        if ($invalidFormat) {
            Throw new InvalidRepoException('Repo name is in an invalid format (no slash)');
        }



        return $valid;
    }
}