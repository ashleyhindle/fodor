<?php

use App\Fodor\Input;

class InputTest extends TestCase
{
    /** @test */
    public function it_returns_true_for_string()
    {
        $this->assertTrue((new Input([
            'type' => 'string',
        ]))->validate('I am a string'));
    }

    /** @test */
    public function it_returns_false_for_string_with_type_number()
    {
        $this->assertFalse((new Input([
            'type' => 'number',
        ]))->validate('I am a string'));
    }

    /** @test */
    public function it_returns_true_for_number_with_type_number()
    {
        $this->assertTrue((new Input([
            'type' => 'number',
        ]))->validate('42'));

        $this->assertTrue((new Input([
            'type' => 'number',
        ]))->validate(42));
    }

    /** @test */
    public function it_returns_true_for_valid_regex()
    {
        $this->assertTrue((new Input([
            'type' => 'regex',
            'regex' => '^([a-z]{3})([0-9]{3})$'
        ]))->validate('abc123'));
    }

    /** @test */
    public function it_returns_false_for_invalid_regex()
    {
        $this->assertFalse((new Input([
            'type' => 'regex',
            'regex' => '^[a-z]{1}$'
        ]))->validate('abc123'));
    }

    /** @test */
    public function it_returns_false_for_type_regex_with_no_regex_provided()
    {
        $this->setExpectedException('LogicException');
        $this->assertFalse((new Input([
            'type' => 'regex',
        ]))->validate('abc123'));
    }

    /** @test */
    public function it_returns_true_for_type_select_with_valid_option()
    {
        $this->assertTrue((new Input([
            'type' => 'select',
            'options' => ['abc', 'xyz', '123']
        ]))->validate('xyz'));
    }

    /** @test */
    public function it_returns_false_for_type_select_with_invalid_option()
    {
        $this->assertFalse((new Input([
            'type' => 'select',
            'options' => ['abc', 'xyz', '123']
        ]))->validate('Non-conformity'));
    }

    /** @test */
    public function it_returns_false_for_type_select_with_no_options()
    {
        $this->setExpectedException('LogicException');
        $this->assertFalse((new Input([
            'type' => 'select',
        ]))->validate('Non-conformity'));
    }

    /** @test */
    public function it_returns_true_for_valid_email()
    {
        $this->assertTrue((new Input([
            'type' => 'email'
        ]))->validate('ashley@fodor.xyz'));
    }

    /** @test */
    public function it_returns_false_for_invalid_email()
    {
        $this->assertFalse((new Input([
            'type' => 'email'
        ]))->validate('Non-conformity'));
    }

    /** @test */
    public function it_returns_true_for_valid_url()
    {
        $this->assertTrue((new Input([
            'type' => 'url'
        ]))->validate('https://fodor.xyz'));
    }

    /** @test */
    public function it_returns_true_for_invalid_url()
    {
        $this->assertFalse((new Input([
            'type' => 'url'
        ]))->validate('fodor.xyz'));
    }

}