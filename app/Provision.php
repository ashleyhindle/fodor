<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Provision extends Model
{
    public $timestamps = false;
    public $inputs = [];

    public function domain()
    {
        return $this->subdomain . '.fodor.xyz';
    }
}
