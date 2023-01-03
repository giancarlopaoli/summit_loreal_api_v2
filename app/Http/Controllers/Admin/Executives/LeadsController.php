<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Enums;
use Carbon\Carbon;
use App\Models\Client;
use App\Models\DocumentType;
use App\Models\Lead;
use App\Models\LeadContact;
use App\Models\LeadStatus;
use App\Models\LeadContactType;
use App\Models\ContactData;
use App\Models\TrackingStatus;
use App\Models\TrackingPhase;
use App\Models\TrackingForm;
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
            'tracking_phase_id' => TrackingPhase::where('name', 'Primer seguimiento')->first()->id,
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
              'main_contact' => 1,
              'created_by' => auth()->id()
            ]);

            if($lead_contact){
                if(isset($request->phone)){
                    $contact_data = ContactData::create([
                        'lead_contact_id' => $lead_contact->id,
                        'type' => 'Celular',
                        'contact' => $request->phone,
                        'created_by' => auth()->id()

                    ]);
                }

                if(isset($request->email)){
                    $contact_data = ContactData::create([
                        'lead_contact_id' => $lead_contact->id,
                        'type' => 'Email',
                        'contact' => $request->email,
                        'created_by' => auth()->id()
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

    public function statuses(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                "lead_statuses" => LeadStatus::select("id","name")->get()
            ]
        ]);
    }

    public function tracking_phases(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                "tracking_phases" => TrackingPhase::select("id","name","min_days","max_days")->get()
            ]
        ]);
    }

    public function tracking_statuses(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                "tracking_statuses" => TrackingStatus::select("id","name")->get()
            ]
        ]);
    }

    public function tracking_forms(Request $request) {

        return response()->json([
            'success' => true,
            'data' => [
                "tracking_forms" => TrackingForm::select("id","name")->get()
            ]
        ]);
    }

    public function list(Request $request) {
        $val = Validator::make($request->all(), [
            'contact_type' => 'nullable|in:Natural,Juridica',
            'lead_contact_type_id' => 'nullable|exists:lead_contact_types,id',
            'document_type_id' => 'nullable|exists:document_types,id',
            'region_id' => 'nullable|exists:regions,id',
            "sector_id" => 'nullable|exists:sectors,id',
            "lead_status_id" => 'nullable|exists:lead_statuses,id',
            "company_name" => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $leads = Lead::where('executive_id', auth()->id())
            ->with('document_type:id,name')
            ->with('lead_contact_type:id,name')
            ->with('lead_status:id,name')
            ->with('region:id,name')
            ->with('sector:id,name')
            ->with('executive.user:id,name,last_name')
            ->with('creator:id,name,last_name');

        $data = $request->all();

        foreach ($data as $key => $value) {
            if($value != ""){
                if(Str::startsWith($key, ["lead_contact_type_id", "region_id", "contact_type", "lead_status_id", "document_type_id"]) ){
                    $leads = $leads->where($key, $value);
                }
                else{
                    $leads = $leads->where($key, 'like', '%' .$value.'%');
                }
            }
        }

        $leads = $leads->get();

        return response()->json([
            'success' => true,
            'data' => [
                'leads' => $leads
            ]
        ]);
    }


    public function lead_detail(Request $request, Lead $lead) {

        $lead = Lead::where('id', $lead->id)
            ->with('document_type:id,name')
            ->with('lead_contact_type:id,name')
            ->with('lead_status:id,name')
            ->with('region:id,name')
            ->with('sector:id,name')
            ->with('executive.user:id,name,last_name')
            ->with('creator:id,name,last_name')
            ->with('contacts:id')
            ->with('contacts.data:id,lead_contact_id,type,contact,created_by')
            ->with('trackings','trackings.tracking_status:id,name')
            ->with('trackings.tracking_form:id,name')
            ->first();


        return response()->json([
            'success' => true,
            'data' => [
                'lead' => $lead
            ]
        ]);
    }

    public function new_contact(Request $request, Lead $lead) {
        $val = Validator::make($request->all(), [
            'names' => 'required|string',
            'last_names' => 'required|string',
            'area' => 'required|string',
            'job_title' => 'required|string',
            'contact_data' => 'required|array'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $lead_contact = $lead->contacts()->create([
          'lead_id' => $request->lead_id,
          'names' => $request->names,
          'last_names' => $request->last_names,
          'area' => $request->area,
          'job_title' => $request->job_title,
          'main_contact' => 0,
          'created_by' => auth()->id()
        ]);

        if($lead_contact){
            foreach ($request->contact_data as $key => $value) {
                if($value['type'] != "" && $value['contact'] != ""){
                    $lead_contact->data()->create([
                      'type' => $value['type'] ,
                      'contact' => $value['contact'],
                      'created_by' => auth()->id()
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'data' => $lead_contact 
        ]);
    }

    public function new_follow(Request $request, Lead $lead) {
        $val = Validator::make($request->all(), [
            'tracking_status_id' => 'required|exists:tracking_statuses,id',
            'tracking_form_id' => 'required|exists:tracking_forms,id',
            'lead_contact_id' => 'nullable|exists:lead_contacts,id',
            'comments' => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $tracking = $lead->trackings()->create([
              'tracking_status_id' => $request->tracking_status_id,
              'tracking_form_id' => $request->tracking_form_id,
              'tracking_phase_id' => $lead->tracking_phase_id,
              'lead_contact_id' => $request->lead_contact_id,
              'comments' => $request->comments,
              'created_by' => auth()->id(),
        ]);

        if($tracking){
            if(TrackingStatus::where('id', $request->tracking_status_id)->first()->name == 'No contesta') $tracking_status = "En curso";
            elseif(TrackingStatus::where('id', $request->tracking_status_id)->first()->name == 'Seguimiento incumplido') $tracking_status = 'Seguimiento incumplido';
            else $tracking_status = 'Completado';

            $lead->lead_status_id = LeadStatus::where('name', TrackingStatus::where('id', $request->tracking_status_id)->first()->name)->first()->id;
            $lead->tracking_status = $tracking_status;
            $lead->save();

        }

        return response()->json([
            'success' => true,
            'data' => [
                "Seguimiento registrado exitosamente"
            ]
        ]);
    }
}
