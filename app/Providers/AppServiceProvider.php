<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Request;
use App\Role;
use App\Permit;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        Role::saving(function ($role) {
            $permits = Request::get('permits');
            $role->setPermissions([]);
            if (isset($permits)){
                foreach($permits as $permitid)
                {
                    $permit = Permit::find($permitid);
                    $role->addPermission($permit->slug);
                }
            }
            if (!$permits) return;
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
