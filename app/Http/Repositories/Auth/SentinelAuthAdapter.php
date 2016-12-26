<?php namespace App\Http\Repositories\Auth;

use Exception;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Cartalyst\Sentinel\Users\UserInterface;
use Tymon\JWTAuth\Providers\Auth\AuthInterface;

class SentinelAuthAdapter implements AuthInterface
{
    /**
     * Check a user's credentials
     *
     * @param  array  $credentials
     * @return bool
     */
    public function byCredentials(array $credentials = [])
    {
        try {
            $user = Sentinel::authenticate($credentials);
            return $user instanceof UserInterface;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Authenticate a user via the id
     *
     * @param  mixed  $id
     * @return bool
     */
    public function byId($id)
    {
        try {
            $user = Sentinel::findById($id);
            Sentinel::login($user);
            return $user instanceof UserInterface && Sentinel::check();
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get the currently authenticated user
     *
     * @return mixed
     */
    public function user()
    {
        return Sentinel::getUser();
    }
}