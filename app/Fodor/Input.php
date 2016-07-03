<?php namespace App\Fodor;
/*
Valid types: url, email, regex, select, string, number

Input array:
{
      "title": "Registration token",
      "name": "REGISTRATION_TOKEN",
      "placeholder": "xxxxxxxxx-xxxx",
      "type": "regex",
      "notes": "Token from http://gitlab.example.com/admin/runners", (optional)
      "regex": "[a-zA-Z\\-0-9]+", (required for type regex)
      "options": [
        'blue', 'green', 'red'
      ] (required for type select)

}
*/
class Input
{
    private $validTypes = [
        'url',
        'email',
        'regex',
        'select',
        'string',
        'number',
    ];

    private $input;

    public function __construct(array $input)
    {
        $this->input = $input;
    }

    public function validate($value)
    {
        switch($this->input['type']) {
            case 'url':
                return filter_var($value, FILTER_VALIDATE_URL) !== false; // Doesn't work with international chars
            case 'number':
                return is_numeric($value);
            case 'email':
                return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            case 'regex':
                if (empty($this->input['regex'])) {
                    Throw new \LogicException('Must provide a regex with the key "regex" when using type regex');
                }

                $delimiter = $this->getPregDelimiter($this->input['regex']);
                if ($delimiter === false) {
                    Throw new \LogicException('Couldn\'t find a delimiter to use, your regex uses them all');
                }

                return preg_match($delimiter . $this->input['regex'] .$delimiter, $value) === 1;
            case 'select':
                if (!isset($this->input['options'])) {
                    Throw new \LogicException('Must provide an options array when using type select');
                }
                return in_array($value, $this->input['options']);
            case 'string':
            default:
                return true;
        }
    }

    public function getPregDelimiter($regex)
    {
        $options = ['/', '%', '#'];
        foreach ($options as $option) {
            if (strpos($regex, $option) === false) {
                return $option;
            }
        }

        return false;
    }
}