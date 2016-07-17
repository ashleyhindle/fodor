<?php namespace App\Fodor;


class Config
{
    private $filename = 'fodor.json';
    private $data;
    private $json; // string of json

    /**
     * Config constructor.
     * @string $json This should be a string of JSON
     */
    public function __construct($json)
    {
        $this->data = json_decode($json, true);
        $this->json = $json;
    }

    public function getJson()
    {
        return $this->json;
    }

    public function valid()
    {
        if (is_null($this->data) || $this->data === false) {
            Throw new \Exception('This repo\'s fodor.json is invalid');
        }

        if (empty($this->data['provisioner'])) {
            Throw new \Exception('This repo\'s fodor.json doesn\'t provide a provisioner');
        }

        if (empty($this->data['description'])) {
            Throw new \Exception('This repo\'s fodor.json doesn\'t provide a description');
        }

        return true;
    }

    public function __get($name)
    {
        return (array_key_exists($name, $this->data)) ? $this->data[$name] : null;
    }

    function __isset($name)
    {
        return (array_key_exists($name, $this->data)) ? true : false;
    }
}