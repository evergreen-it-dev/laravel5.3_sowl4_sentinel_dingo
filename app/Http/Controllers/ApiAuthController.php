<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Redirect;
use Sentinel;
use Activation;
use Reminder;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Validator;
use Mail;
use Storage;
use CurlHttp;
use Dingo\Api\Routing\Helpers;

class ApiAuthController extends Controller
{
    use Helpers;

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'), 200);
    }

    public function refreshToken(){
        $token = JWTAuth::getToken();

        if(!$token){
            return $this->response->error("Token is invalid")->setStatusCode(401);
        }

        try {
            $refreshed_token = JWTAuth::refresh($token);
        } catch (JWTException $ex) {
            return $this->response->error("Something went wrong with token")->setStatusCode(401);
        }

        return $this->response->array(compact('refreshed_token'))->setStatusCode(200);
    }

    public function register(Request $request)
    {
        $input = $request->all();
        $messages = [
            'email.required'            => 'Email required',
            'email.email'               => 'It appears that the email entered with error',
            'password.required'         => 'Enter password',
            'password.between'          => 'Minimum password length of 8 characters',
            'password_confirm.required' => 'Enter password',
            'password_confirm.same'     => 'The entered passwords do not match',
            'password_confirm.between'  => 'Minimum password length of 8 characters',
        ];
        $validator = Validator::make($input, [
            'email' => 'required|email',
            'password' => 'required|between:8,20',
            'password_confirm' => 'required|same:password|between:8,20',
        ], $messages);

        if(count($validator->errors()->all()) > 0){
            return $this->response->array(['validation_error' => 1, 'error_msgs' => $validator->errors()->all()])->setStatusCode(422);
        }

        $credentials = [ 'email' => $request->email ];
        if($user = Sentinel::findByCredentials($credentials))
        {
            return $this->response->error("This Email is already registered", 200);
        }

        if ($sentuser = Sentinel::register($input))
        {
            $activation = Activation::create($sentuser);
            $code = $activation->code;
            $sent = Mail::send('mail.account_activate_app', compact('sentuser', 'code'), function($m) use ($sentuser)
            {
                $m->from('noreplqy@mysite.com', 'MySite');
                $m->to($sentuser->email)->subject('Account activation');
            });
            if ($sent === 0)
            {
                return $this->response->error("Activation email is not sending", 200);
            }

            $role = Sentinel::findRoleBySlug('user');
            $role->users()->attach($sentuser);

            return $this->response->array(['success' => 1, 'msg' => 'Your account has been created. Check Email to activate.'])->setStatusCode(200);
        }
        return $this->response->error("Failed to register", 200);
    }
}