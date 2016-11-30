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

    /** @test */
    public function it_replaces_domain_with_non_empty_domain()
    {
        $json = '{
            "text": {
                "complete": "Complete Text {{DOMAIN}} end"
            }
        }';

        $domain = 'fluffy-cloud-4853-182.fodor.xyz';
        $expected = "Complete Text {$domain} end";

        $result = (new Config($json))->getText(
            'complete',
            ['{{DOMAIN}}' => $domain]
        );

        $this->assertEquals(
            $result,
            $expected,
            'Fodor config did not replace domain like I wanted it to, how rude!'
        );
    }

    /** @test */
    public function it_returns_an_empty_string_on_get_text_with_invalid_key()
    {
        $json = '{
            "text": {
                "complete": "Complete Text {{DOMAIN}} end"
            }
        }';

        $result = (new Config($json))->getText(
            'burger-house',
            ['{{DOMAIN}}' => 'notempty.xyz']
        );

        $this->assertEquals(
            $result,
            '',
            'Fodor config did not return empty string with invalid key'
        );
    }

    /** @test */
    public function it_returns_correclt_when_get_text_with_valid_key_and_no_replacements()
    {
        $completeText = 'Complete Text {{DOMAIN}} end';

        $json = '{
            "text": {
                "complete": "' . $completeText . '"
            }
        }';

        $result = (new Config($json))->getText('complete');

        $this->assertEquals(
            $result,
            $completeText,
            'Fodor config did not return the same string with no replacements'
        );
    }
}