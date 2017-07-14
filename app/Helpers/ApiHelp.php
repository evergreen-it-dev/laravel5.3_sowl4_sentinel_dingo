<?php
namespace App\Helpers;

class ApiHelp
{
    public static function errorResponse($error, $error_description, $http_status_code = 401)
    {
        if (!is_array($error_description)) {
            $error_description = array($error_description);
        }

        $response_data = [
            'error' => $error,
            'error_description' => $error_description,
            'data_time' => time(),
        ];

        return response()->json($response_data, $http_status_code)->header('Access-Control-Allow-Origin', '*')->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')->header('Access-Control-Allow-Headers', 'Content-Type,x-prototype-version,x-requested-with');
    }

    public static function successResponse($data, $http_status_code = 200)
    {
        $response_data = [
            'data_time' => time(),
            'result' => $data,
        ];

        return response()->json($response_data, $http_status_code)->header('Access-Control-Allow-Origin', '*')->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')->header('Access-Control-Allow-Headers', 'Content-Type,x-prototype-version,x-requested-with');
    }
}