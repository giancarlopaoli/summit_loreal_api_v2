<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Agenda;
use App\Models\AgendaSpeaker;
use App\Models\AgendaCategory;
use Illuminate\Support\Facades\Validator;

class AgendaController extends Controller
{
    //
    public function list (Request $request) {
        $agenda = AgendaCategory::select('agenda_categories.id','name_en','name_es','start_date')->with([
             'agenda:id,agenda_category_id,start_date,subject_pen,subject_usd' => [
                 'speakers:id,name,agenda_id,specialty_usd,specialty_pen'
             ]
        ])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'agenda' => $agenda
            ]
        ]);
    }

    public function speaker_detail (Request $request, AgendaSpeaker $agenda_speaker) {

        return response()->json([
            'success' => true,
            'data' => [
                'agenda' => $agenda_speaker->load('agenda:id,agenda_category_id,start_date,subject_pen,subject_usd')
            ]
        ]);
    }

    public function edit_agenda_category (Request $request, AgendaCategory $agenda_category) {
        $val = Validator::make($request->all(), [
            'start_date' => 'required|date',
            'name_es' => 'nullable|string',
            'name_en' => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $agenda_category->start_date = $request->start_date;
        $agenda_category->name_es = (is_null($request->name_es) ? "" : $request->name_es);
        $agenda_category->name_en = (is_null($request->name_en) ? "" : $request->name_en);
        $agenda_category->save();

        return response()->json([
            'success' => true,
            'data' => [
                'agenda_category' => $agenda_category
            ]
        ]);
    }

    public function edit_agenda (Request $request) {
        $val = Validator::make($request->all(), [
            'agenda_id' => 'required|exists:agendas,id',
            'start_date' => 'required|date',
            'subject_pen' => 'required|string',
            'subject_usd' => 'required|string',
            'agenda_speaker_id' => 'nullable|exists:agenda_speakers,id',
            'name' => 'nullable|string',
            'specialty_pen' => 'nullable|string',
            'specialty_usd' => 'nullable|string'
        ]);
        if($val->fails()) return response()->json($val->messages());

        $agenda = Agenda::find($request->agenda_id);

        $agenda->start_date = $request->start_date;
        $agenda->subject_pen = $request->subject_pen;
        $agenda->subject_usd = $request->subject_usd;
        $agenda->save();

        if(!is_null($request->agenda_speaker_id)){
            $agenda_speaker = AgendaSpeaker::find($request->agenda_speaker_id);

            $agenda_speaker->name = $request->name;
            $agenda_speaker->specialty_pen = $request->specialty_pen;
            $agenda_speaker->specialty_usd = $request->specialty_usd;
            $agenda_speaker->save();
        }

        return response()->json([
            'success' => true,
            'data' => [
                'agenda' => $agenda->load('speakers')
            ]
        ]);
    }
}
