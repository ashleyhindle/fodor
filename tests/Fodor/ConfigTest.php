<?php use App\Fodor\Config;

class ConfigTest extends TestCase
{

    /** @test */
    public function it_returns_invalid_for_invalid_json()
    {
        $this->setExpectedException('\Exception');
        (new Config("Invalid json"))->valid();
    }

    /** @test */
    public function it_returns_invalid_for_valid_json_with_missing_provisioner()
    {
        $this->setExpectedException('\Exception');
        (new Config('{"description": "Hi"}'))->valid();
    }

    /** @test */
    public function it_returns_invalid_for_valid_json_with_missing_description()
    {
        $this->setExpectedException('\Exception');
        (new Config('{"provisioner": "provisioner.sh"}'))->valid();
    }

    /** @test */
    public function it_returns_the_same_json_as_provided()
    {
        $json = '{"hey": "Mickey", "you": "So fine"}';
        $this->assertEquals($json, (new Config($json))->getJson());
    }

    /** @test */
    public function it_gives_access_to_underlying_data()
    {
        $json = '{"hey": "Mickey", "you": "So fine"}';
        $this->assertEquals('Mickey', (new Config($json))->hey);
    }

    /** @test */
    public function it_returns_null_when_accessing_nonexistent_values()
    {
        $json = '{"hey": "Mickey", "you": "So fine"}';
        $this->assertNull((new Config($json))->heyIDontExist);
    }

    /** @test */
    public function it_returns_true_when_isset_on_existent_properties()
    {
        $json = '{"hey": "Mickey", "you": "So fine"}';
        $result = (new Config($json))->hey;
        $this->assertTrue(isset($result));
    }

    /** @test */
    public function it_returns_false_when_isset_on_nonexistent_properties()
    {
        $json = '{"hey": "Mickey", "you": "So fine"}';
        $result = (new Config($json))->heyIDontExist;
        $this->assertFalse(isset($result));
    }
}