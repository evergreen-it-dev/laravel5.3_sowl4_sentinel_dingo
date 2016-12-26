<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Permit extends Model
{
    public function roles()
    {
        return $this->belongsToMany('App\Role');
    }
}
