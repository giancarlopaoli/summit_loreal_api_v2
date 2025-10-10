<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Country;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use App\Mail\test;

class RegisterController extends Controller
{
    //
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'last_name' => 'required|string',
            'email' => 'required|unique:users,email',
            'phone' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'type' => 'required|in:Participante,Expositor,Administrador',
            'document_type' => 'required|string',
            'document_number' => 'required|string',
            'preferences' => 'required|string',
            'password' => 'required|string',
            'accepts_publicity' => 'required|string'
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $user = User::create([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'preferences' => $request->preferences,
            'type' => $request->type,
            'password' =>  Hash::make($request->password),
            'accepts_publicity' => $request->accepts_publicity
        ]);

        //$rpta_mail = Mail::to($request->email)->bcc('giancarlopaoli@gmail.com')->send(new RegisterNotification());

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $user
            ]
        ]);

        /*return response()->json([
            'success' => false,
            'data' => [
                'El evento ha finalizado'
            ]
        ]);*/
    }

    public function massive_register(Request $request) {
        $data = Storage::get('users.json'); 
        $jsonData = json_decode($data, true);


        foreach ($jsonData as $key => $value) {
            $insert = User::create(
                $mergedArray = array_merge(
                    $value, 
                    [
                        'password' => Hash::make('password'),
                        'accepts_publicity' => 1
                    ]
                ) 
            );

            /*return response()->json([
                //'success' => true,
                $insert
            ]);*/
        }

        return response()->json([
            'success' => true,
            'data' => [
                $insert
            ]
        ]);
    }

    public function image_format(Request $request) {
        

        //$user = User::find(21);
        $users = User::where('id','>=', 230)
            ->where('id', '<=', 300)
            ->where('confirmed', 0)
            ->get();

        foreach ($users as $user) {
            //$image = Storage::get('1.jpeg'); 
            //$image = file_get_contents($user->image);
            $fileurl = $user->image;

            /*$tmp = explode("/", $fileurl);
            $size = sizeof($tmp);
            $fileName = $tmp[$size - 1];*/

            $fileName = $user->id.".png";
            
            //Storage::disk('local')->put("tpm-".$fileName, $image);

            if($fileName != 'logoloreal.jpeg'){
                //$filePath = storage_path('app\\rslt\\'.$fileName);
                $filePath = storage_path('app\\rslt\\'.$fileName);

                // Utilizando https://www.ailabtools.com/
                $consulta = Http::withHeaders([
                            //'Content-Type' => 'multipart/form-data',
                            'ailabapi-api-key' => env('API_AILAB_TOKEN')
                        ])
                        ->attach('image', file_get_contents($filePath), $fileName)
                        ->post("https://www.ailabapi.com/api/portrait/effects/portrait-animation?type=sketch");
                        //->post("https://www.ailabapi.com/api/cutout/portrait/avatar-extraction");

                $rpta_json = json_decode($consulta);

                /*$rpta_json = json_decode('{
                    "data": {
                        "image_url": "https://ai-result-rapidapi.ailabtools.com/faceBody/portraitAnimation/2025-10-08/012100-97ea4d3e-1005-ee68-53f5-5b8054aa4cd6-1759857660.png"
                    },
                    "error_code": 0,
                    "error_detail": {
                        "status_code": 200,
                        "code": "",
                        "code_message": "",
                        "message": ""
                    },
                    "log_id": "77496025",
                    "request_id": "A6697900-DAA5-510F-BF1E-12F51DAD40D1"
                }');*/


                if($rpta_json->error_code != 0){
                    $user->image_error_code = $rpta_json->error_code;
                    $user->error_message = $rpta_json->error_msg;
                    $user->save();
                }
                else{
                    logger('Imagen success: image_format@RegisterController', ["user_id" => $user->id, "rpta_api" => $rpta_json]);

                    if(!is_null($rpta_json->data)){
                        $image_rslt = file_get_contents($rpta_json->data->image_url);
                        //$image_rslt = file_get_contents($rpta_json->data->elements[0]->image_url);
                        
                        $filenamerslt = $user->id.".png";
                        Storage::disk('local')->put('rslt-sketch/'.$filenamerslt, $image_rslt);

                        //$path = 'public/loreal/images/profilecartoon';
                        //$s3 = Storage::disk('s3')->putFileAs($path, $image_rslt, $fileName, 'public');

                        //$user->image = 'https://signme4.s3.us-east-1.amazonaws.com/public/loreal/images/profilecartoon/'.$filenamerslt;
                        $user->confirmed = 1;
                        $user->save();
                    }
                    else{
                        $user->error_message = 'error desconocido';
                        $user->save();

                        logger('error procesamiento imagen: image_format@RegisterController', ["user_id" => $user->id, "rpta_api" => $rpta_json]);
                    }
                }
            }
        }

            

        return response()->json([
            'success' => true,
            'data' => [
                $rpta_json
            ]
        ]);
    }

    public function extract_head(Request $request) {
        

        $users = User::where('id','>=', 200)
            ->where('id', '<=', 300)
            ->where('confirmed', 0)
            //->whereNotin('id', [3,4,14,20,23,24,35,37])
            ->get();

        foreach ($users as $user) {
            //$image = Storage::get('1.jpeg'); 
            //$image = file_get_contents($user->image);
            /*$fileurl = $user->image;

            $tmp = explode("/", $fileurl);
            $size = sizeof($tmp);
            $fileName = $tmp[$size - 1];*/

            $fileName = $user->id.".jpg";
            
            //Storage::disk('local')->put("tpm-".$fileName, $image);

            if($fileName != 'logoloreal.jpeg'){
                $filePath = storage_path('app\\head-wbg\\'.$fileName);

                // Utilizando https://www.ailabtools.com/
                $consulta = Http::withHeaders([
                            //'Content-Type' => 'multipart/form-data',
                            'ailabapi-api-key' => env('API_AILAB_TOKEN')
                        ])
                        ->attach('image', file_get_contents($filePath), $fileName)
                        //->post("https://www.ailabapi.com/api/portrait/effects/portrait-animation?type=head");
                        ->post("https://www.ailabapi.com/api/cutout/portrait/portrait-background-removal");

                $rpta_json = json_decode($consulta);


                if($rpta_json->error_code != 0){
                    $user->image_error_code = $rpta_json->error_code;
                    $user->error_message = $rpta_json->error_msg;
                    $user->save();
                }
                else{
                    logger('Imagen success: image_format@RegisterController', ["user_id" => $user->id, "rpta_api" => $rpta_json]);

                    if(!is_null($rpta_json->data)){
                        $image_rslt = file_get_contents($rpta_json->data->image_url);
                        //$image_rslt = file_get_contents($rpta_json->data->elements[0]->image_url);
                        
                        $filenamerslt = $user->id.".png";
                        Storage::disk('local')->put('rslt/'.$filenamerslt, $image_rslt);

                        $path = 'public/loreal/images/profilecartoon';
                        //$s3 = Storage::disk('s3')->putFileAs($path, $image_rslt, $fileName, 'public');

                        //$user->image = 'https://signme4.s3.us-east-1.amazonaws.com/public/loreal/images/profilecartoon/'.$filenamerslt;
                        $user->confirmed = 1;
                        $user->save();
                    }
                    else{
                        $user->error_message = 'error desconocido';
                        $user->save();

                        logger('error procesamiento imagen: image_format@RegisterController', ["user_id" => $user->id, "rpta_api" => $rpta_json]);
                    }
                }
            }
        }

            

        return response()->json([
            'success' => true,
            'data' => [
                //$rpta_json
            ]
        ]);
    }

    public function change_names(Request $request) {
        

        $users = User::where('id','>=', 51)
            ->where('id', '<=', 300)
            ->get();

        foreach ($users as $user) {
            $fileurl = $user->image;

            $tmp = explode("/", $fileurl);
            $size = sizeof($tmp);
            $fileName = $tmp[$size - 1];


            if($fileName != 'logoloreal.jpeg'){

                $filenamerslt = $user->id.".png";

                $user->image = 'https://signme4.s3.us-east-1.amazonaws.com/public/loreal/images/profilecartoon/'.$filenamerslt;
                $user->save();


                $filePath = storage_path('app\\rslt-sketch\\'.$filenamerslt);
                $image_rslt = file_get_contents($filePath);

                $filewithname = $user->name." ".$user->last_name." - ".$filenamerslt;

                Storage::disk('local')->put('rslt-names/'.$filewithname, $image_rslt);
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                $fileName
            ]
        ]);
    }

    public function countries(Request $request) {
        return response()->json([
            'success' => true,
            'data' => Country::select('id','name','prefix','phone_code')->get()
        ]);
    }

    public function test(Request $request) {

        $rpta_mail = Mail::send(new test());

        return response()->json([
            'success' => true,
            'data' => Country::select('id','name','prefix','phone_code')->get()
        ]);
    }


}