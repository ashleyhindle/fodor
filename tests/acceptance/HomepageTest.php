<?php

class HomepageTest extends TestCase
{
    use \Illuminate\Foundation\Testing\DatabaseMigrations;
    /** @test */
    public function it_loads_the_homepage()
    {
        $this->visit('/')
            ->see('What is Fodor?');
    }

    /** @test */
    public function it_loads_a_provision_page()
    {
        if (getenv('TRAVIS') === true) { // we need to skip this for now as we don't have a github key/secret, so we can't get the fodor.json file so this page fails
            $this->markTestSkipped('This test should not run if on Travis.');
        }
        $this->visit('/provision/fodorxyz/mattermost')
            ->see("provision '<strong>fodorxyz/mattermost</strong>'");
    }
}