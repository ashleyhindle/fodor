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

    public function testResultMatchesRegexBasedOnParams()
    {
        $result = Haikunator::haikunate(['delimiter' => '@', 'tokenLength' => 19, 'tokenChars' => 'z', 'suffix' => 'oops']);
        $this->assertRegExp('/[a-z]+@[a-z]+@z{19}@oops/', $result);

        $result = Haikunator::haikunate(['delimiter' => '(', 'tokenLength' => 3, 'tokenChars' => 'zoo', 'suffix' => 'fodor']);
        $this->assertRegExp('/[a-z]+\([a-z]+\([zo]{3}\(fodor/', $result);
    }
}