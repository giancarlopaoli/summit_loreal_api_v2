<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class BanksController extends Controller
{
    // Banks List
    Public function bank_list() {

        $banks = Bank::select('id','name','shortname','corfid_id','main','active','image')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'banks' => $banks
            ]
        ]);
    }

    // Banks List
    Public function edit_bank(Request $request, Bank $bank) {

        $bank->update($request->only(["name","shortname","corfid_id","main","active"]));


        return response()->json([
            'success' => true,
            'data' => [
                'bank' => $bank
            ]
        ]);
    }

    public function new_bank(Request $request) {
        $val = Validator::make($request->all(), [
            'name' => 'required|string',
            'shortname' => 'required|string',
            'corfid_id' => 'nullable',
            'file' => 'required|file'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->hasFile('file')){
            $file = $request->file('file');
            $path = 'static/img/';

            
            $filename = $file->getClientOriginalName();

            try {
                $s3 = Storage::disk('s3public')->putFileAs($path, $file, $filename);

                $bank = Bank::create([
                    'name' => $request->name,
                    'shortname' => $request->shortname,
                    'corfid_id' => isset($request->corfid_id) ? $request->corfid_id : 0,
                    'image' => 'https://bill-upload.s3.us-east-1.amazonaws.com/static/img/'.$filename,
                    'active' => 1
                ]);

                return response()->json([
                    'success' => true,
                    'data' => [
                        'bank' => $bank
                    ]
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: BanksController@new_bank', ["error" => $e]);

                return response()->json([
                    'success' => false,
                    'errors' => [
                        'Error en el archivo adjunto'
                    ]
                ]);
            }

        } else{
            return response()->json([
                'success' => false,
                'errors' => [
                    'Error en el archivo adjunto'
                ]
            ]);
        }
    }
}
