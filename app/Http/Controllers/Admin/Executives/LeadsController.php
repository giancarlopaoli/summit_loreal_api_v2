<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Enums;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\LeadStatus;
use App\Models\LeadContactType;
use App\Models\ContactData;
use App\Http\Controllers\Register\RegisterController;

class LeadsController extends Controller
{
    // Validating if the ruc exists
    public function exists_company(Request $request) {
        $val = Validator::make($request->all(), [
            'document_number' => 'required|string|size:11'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $client = Client::where('document_type_id', DocumentType::where('name','RUC')->first()->id)->where('document_number', $request->document_number)->get();

        $lead = Lead::where('document_number', $request->document_number)->get();

        $consult = new RegisterController();
        $result = $consult->function_validate_ruc($request->document_number)->getData();

        $company_name = null;

        if(is_object($result)){
            if($result->success){
                $company_name = $result->data->ruc->business;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exists' => (count($client) >0 || count($lead) > 0) ? true : false,
                'company_name' => $company_name
            ]
        ]);
    }

    // Validating if the person exists
    public function exists_person(Request $request) {
        $val = Validator::make($request->all(), [
            'document_type_id' => 'required|string',
            'document_number' => 'required|string'
        ]);
        if($val->fails()) return response()->json($val->messages());


        $client = Client::where('document_type_id', $request->document_type_id)->where('document_number', $request->document_number)->get();

        $lead = Lead::where('document_type_id', $request->document_type_id)->where('document_number', $request->document_number)->get();

        $consult = new RegisterController();
        $result = $consult->function_validate_dni($request->document_number)->getData();

        $name = null;
        $last_name = null;

        if(is_object($result)){
            if($result->success){
                $name = $result->data->dni->name;
                $last_name = $result->data->dni->last_name . " " . $result->data->dni->mothers_name;
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'exists' => (count($client) >0 || count($lead) > 0) ? true : false,
                'name' => $name,
                'last_name' => $last_name,
            ]
        ]);
    }

    //Register Lead
    public function register_lead(Request $request) {
        $val = Validator::make($request->all(), [
            'contact_type' => 'required|in:Natural,Juridica',
            'document_type_id' => 'required|exists:document_types,id',
            'region_id' => 'nullable|exists:regions,id',
            "sector_id" => 'nullable|exists:sectors,id'
        ]);
        if($val->fails()) return response()->json($val->messages());

        logger('Registro Prospecto: register_lead@LeadsController', ["lead" => $request->all()]);

        $lead = Lead::create([
            'contact_type' => $request->contact_type,
            'document_type_id' => $request->document_type_id,
            'region_id' => (isset($request->region_id) ? $request->region_id : null),
            'sector_id' => (isset($request->sector_id) ? $request->sector_id : null),
            'company_name' => ($request->contact_type == 'Natural') ? $request->name . " " . $request->last_name : $request->company_name,
            'document_number' => $request->document_number,
            'lead_contact_type_id' => $request->lead_contact_type_id,
            'lead_status_id' => LeadStatus::where('name', 'Registrado')->first()->id,
            'comments' => $request->comments,
            'executive_id' => auth()->id(),
            'tracking_status' => Enums\TrackingStatus::Pendiente,
            'tracking_date' => Carbon::now(),
            'created_at' => Carbon::now(),
            'created_by' => auth()->id()
        ]);

        if($lead){
            $lead_contact = LeadContact::create([
              'lead_id' => $lead->id,
              'names' => $request->name,
              'last_names' => $request->last_name,
              'area' => (isset($request->area) ? $request->area : null),
              'job_title' => (isset($request->job_title) ? $request->job_title : null),
              'main_contact' => 1
            ]);

            if($lead_contact){
                if(isset($request->phone)){
                    $contact_data = ContactData::create([
                        'lead_contact_id' => $lead_contact->id,
                        'type' => 'Celular',
                        'contact' => $request->phone

                    ]);
                }

                if(isset($request->email)){
                    $contact_data = ContactData::create([
                        'lead_contact_id' => $lead_contact->id,
                        'type' => 'Email',
                        'contact' => $request->email
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                "Prospecto registrado exitosamente"
            ]
        ]);
    }

}
