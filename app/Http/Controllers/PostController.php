<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Post;
use Dingo\Api\Routing\Helpers;

class PostController extends Controller
{
    use Helpers;

    public function getPosts(){
        $user = JWTAuth::parseToken()->authenticate();

        if(!$user){
            return $this->response->errorNotFound("authenticate");
        }

        $data = Post::all();

        return $this->response->array(compact('data'))->setStatusCode(200);
    }
}