<?php

use App\Fodor\Repo;

class RepoTest extends TestCase
{
    /** @test */
    public function it_parses_repo_name_correctly()
    {
        $repo = new Repo('ashleyhindle/rocks');
        $this->assertEquals('ashleyhindle', $repo->getUsername());
        $this->assertEquals('rocks', $repo->getRepoName());
        $this->assertEquals('ashleyhindle/rocks', $repo->getName());
    }

    /** @test */
    public function it_exceptions_with_invalid_format_repo_name()
    {
        $this->setExpectedException(\app\Exceptions\InvalidRepoException::class);
        $repo = new Repo('this is super invalid');
    }

    /** @test */
    public function it_exceptions_with_empty_repo_name()
    {
        $this->setExpectedException(\app\Exceptions\InvalidRepoException::class);
        $repo = new Repo('');
    }
}