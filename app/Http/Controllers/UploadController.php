<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Dingo\Api\Routing\Helpers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Helpers\ApiHelp;
use Validator;
use App\File;


class UploadController extends Controller
{
    use Helpers;

    public function storeFile(Request $request){

        $user = JWTAuth::parseToken()->authenticate();

        if(!$user){
            return ApiHelp::errorResponse('user_not_found', 'User not found', 404);
        }

        if(!$user->inRole('admin')){
            return ApiHelp::errorResponse('forbidden', 'Only for admin', 401);

        }

        $input = $request->all();

        $messages = [
            'file.required' => 'Field File is required',
            'file.file' => 'Field File must be a successfully uploaded file'
        ];

        $rules = [
            'file' => 'required|file'
        ];

        $validator = Validator::make($input, $rules, $messages);

        if (count($validator->errors()->all()) > 0) {
            return ApiHelp::errorResponse('upload_file_validation_error', $validator->errors()->all(), 422);
        }

        $filename_arr = explode(".", $input['file']->getClientOriginalName());

        if(!empty($input['path']) && trim($input['path']) != '') {
            $path = substr(trim($input['path']), -1) == '/' ? substr(trim($input['path']), 0, -1) : trim($input['path']);
        }else{
            $path = '';
        }

        $filename = current($filename_arr) . '-' . round(microtime(true)) . '.' . end($filename_arr);

        $access = (!empty($input['public']) && $input['public'] == 1) ? 'public' : '';

        $s3 = \Storage::disk('s3');

        try{
            $s3->putFileAs($path, $input['file'], $filename, $access);
        }
        catch(\Exception $e){
            return ApiHelp::errorResponse('error_executing_put_object', $e->getMessage(), 500);
        }

        $client = $s3->getDriver()->getAdapter()->getClient();
        $expiry = "+5 minutes";

        $command = $client->getCommand('GetObject', [
            'Bucket' => config('app.aws_bucket'),
            'Key'    => $path . '/' . $filename
        ]);

        $presigned_request = $client->createPresignedRequest($command, $expiry);

        $rds_file = new File();
        $rds_file->name = $path . '/' . $filename;
        $rds_file->save();

        $response_data = [
            's3_temporary_url' => (string) $presigned_request->getUri(),
            'cloudfront_url' => config('app.aws_cloudfront_domain') . '/' . $path . '/' . $filename,
        ];

        if($access == 'public'){
            $response_data['s3_permanent_url'] = $s3->url($path . '/' . $filename);
        }

        return ApiHelp::successResponse($response_data);
    }
}