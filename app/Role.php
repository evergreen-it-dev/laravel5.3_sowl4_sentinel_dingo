<?php

namespace App;

use Cartalyst\Sentinel\Roles\EloquentRole;

class Role extends EloquentRole
{

    public function permits()
    {
        return $this->belongsToMany('App\Permit');
    }

}