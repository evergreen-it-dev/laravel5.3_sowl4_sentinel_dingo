<?php

namespace App;

use Cartalyst\Sentinel\Users\EloquentUser as CartalystUser;
use Hash;

class User extends CartalystUser
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'first_name', 'last_name', 'facebook_id', 'google_id', 'vkontakte_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'created_at', 'updated_at'
    ];

    protected $loginNames = ['email', 'facebook_id', 'google_id', 'vkontakte_id'];

    public function roles()
    {
        return $this->belongsToMany('App\Role', 'role_users', 'user_id', 'role_id');
    }

    public function theroles()
    {
        return $this->belongsToMany('App\Role', 'role_users', 'user_id', 'role_id');
    }

    public function setTherolesAttribute($roles)
    {
        $this->theroles()->detach();
        if ( ! $roles) return;
        if ( ! $this->exists) $this->save();
        $this->theroles()->attach($roles);
    }

    public function getTherolesAttribute($roles)
    {
        return array_pluck($this->theroles()->get(['id'])->toArray(), 'id');
    }

}