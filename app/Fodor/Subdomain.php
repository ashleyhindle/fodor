<?php namespace App\Fodor;

use Cloudflare\Zone\Dns;
use \App\Fodor\Haikunator;

class Subdomain
{
    private $cloudflare;

    public function __construct(\Cloudflare\Api $cloudflareApi) {
        $this->cloudflare = $cloudflareApi;
    }

    public function generateName()
    {
        // TODO: Check appropriate table to ensure it's unique
        return Haikunator::haikunate();
    }

    public function exists($name)
    {
        try {
            $dns = new Dns($this->cloudflare);
            $result = $dns->get($name);
            dd($result);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function create($name, $ip) {
        if (!is_string($name) || !is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        $dns = new Dns($this->cloudflare);
        $result = $dns->createRecord(env('CLOUDFLARE_API_ZONE_ID'), 'A', $name . '.fodor.xyz', $ip, 1);

        if (empty($result)) {
            return false;
        }

        return $result->result;
    }
}