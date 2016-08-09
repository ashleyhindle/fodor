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
        $this->visit('/provision/fodorxyz/mattermost')
            ->see("provision '<strong>fodorxyz/mattermost</strong>'");
    }
}