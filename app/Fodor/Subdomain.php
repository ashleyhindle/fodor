<?php namespace App\Fodor;

class Subdomain
{
    private $dns;

    public function __construct(\Cloudflare\Zone\Dns $dns) {
        $this->dns = $dns;
    }

    public function subdomainAvailable($subdomain) {
        return \App\Provision::where('subdomain', $subdomain)->first() === null;
    }

    public function generateName($suffix='')
    {
        do {
            $subdomain = Haikunator::haikunate(['suffix' => $suffix]);
        } while(! $this->subdomainAvailable($subdomain));

        return $subdomain;
    }

    public function create($name, $ip) {
        if (!is_string($name) || !is_string($ip) || filter_var($ip, FILTER_VALIDATE_IP) === false) {
            return false;
        }

        $result = $this->dns->createRecord(env('CLOUDFLARE_API_ZONE_ID'), 'A', $name . '.fodor.xyz', $ip, 1);

        if (empty($result)) {
            return false;
        }

        return $result->result;
    }
}