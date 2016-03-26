<?php

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Fodor\Haikunator;

class HaikunatorTest extends TestCase
{

    // The only problem with this is that at some point it should fail
    public function testResultIsDifferent()
    {
        $result1 = Haikunator::haikunate();
        $result2 = Haikunator::haikunate();

        $this->assertNotEquals($result1, $result2);
    }
}
