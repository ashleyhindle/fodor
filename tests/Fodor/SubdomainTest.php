<?php

use App\Fodor\Subdomain;

class SubdomainTest extends TestCase
{
    private function getDnsMock()
    {
        $mock = $this->getMock(Cloudflare\Zone\Dns::class, ['createRecord']);
        return $mock;
    }

    private function getSubdomain()
    {
        $subdomain = $this->getMockBuilder(Subdomain::class)
            ->setMethods(['subdomainAvailable'])
            ->setConstructorArgs([$this->getDnsMock()])
            ->getMock();

        return $subdomain;
    }

    /** @test */
    public function it_returns_false_when_trying_to_create_invalid_subdomain()
    {
        $subdomain = $this->getSubdomain();

        $this->assertFalse($subdomain->create('', '0.0.0.0'));
        $this->assertFalse($subdomain->create('', 1269969290));
        $this->assertFalse($subdomain->create('', ''));

        $this->assertFalse($subdomain->create(89, '127.0.0.1'));
        $this->assertFalse($subdomain->create(89, 'not a valid ip'));
        $this->assertFalse($subdomain->create(89,  89));
    }

    /** @test */
    public function it_returns_a_valid_subdomain_on_generateName()
    {
        $subdomain = $this->getSubdomain();

        $subdomain->expects($this->once())
            ->method('subdomainAvailable')->willReturn(true);

        $subdomain = $subdomain->generateName('PINGU');

        $this->assertTrue(is_string($subdomain));
        $this->assertTrue(strpos($subdomain, 'PINGU') !== false);
        $this->assertTrue(strlen($subdomain) > 0);
    }
}