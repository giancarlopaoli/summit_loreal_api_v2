<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Speaker;
use App\Models\AgendaCategory;
use App\Models\Agenda;
use App\Models\Media;
use App\Models\User;
use App\Models\Tour;
use App\Models\Study;
use App\Models\Music;
use App\Models\MusicVote;
use App\Models\Trip;
use App\Models\Recomendation;
use App\Models\RecomendationCategory;
use App\Models\Destiny;
use App\Models\Connectivity;
use App\Models\Survey;
use App\Models\FinalSurvey;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DashboardController extends Controller
{
    public function change_password(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'new_password' => 'required|string'
        ]);
        if ($validator->fails()) return response()->json($validator->messages());

        $user = Auth::user();

        if(!Hash::check($request->old_password, auth()->user()->password)){
            return response()->json([
                'success' => false,
                'errors' => [
                    'La contraseña anterior es incorrecta.'
                ]
            ]);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Contraseña actualizada correctamente'
            ]
        ]);
    }

    public function get_profile (Request $request) {

        $profile = Auth::user();

        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $profile
            ]
        ]);
    }

    public function update_profile (Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'last_name' => 'required|string',
            'phone' => 'required|string',
            'country' => 'required|string',
            'city' => 'required|string',
            'document_type' => 'required|in:DNI,Pasaporte',
            'document_number' => 'required|string',
            'password' => 'required|string',
            'accepts_publicity' => 'required|boolean'
        ]);

        if ($validator->fails()) return response()->json($validator->messages());

        $profile = Auth::user();

        $update = $profile->update([
            'name' => $request->name,
            'last_name' => $request->last_name,
            'phone' => $request->phone,
            'country' => $request->country,
            'city' => $request->city,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'password' =>  Hash::make($request->password),
            'accepts_publicity' => $request->accepts_publicity,
            'confirmed' => 1
        ]);


        return response()->json([
            'success' => true,
            'data' => [
                'profile' => $update
            ]
        ]);
    }

    public function get_speakers (Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $speakers = ($request->language == 'en') ? Speaker::select('id','name','english_description as description','image','document','document2','document3')->get() : Speaker::select('id','name','spanish_description as description','image','document','document2','document3')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'speakers' => $speakers
            ]
        ]);
    }

    public function speaker_detail (Request $request, Speaker $speaker) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $rpta = ($request->language == 'en') ?  Speaker::select('id','name','english_description as description','image','document','document2','document3')->where('id',$speaker->id)->first() : Speaker::select('id','name','spanish_description as description','image','document','document2','document3')->where('id',$speaker->id)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'speakers' => $rpta
            ]
        ]);
    }

    public function get_agenda (Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en',
            //'date' => 'required|date',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $agenda = ($request->language == 'en') ? AgendaCategory::select('agenda_categories.id','name_en as name')->with([
             'agenda:id,agenda_category_id,start_date,subject_usd as subject' => [
                 'speakers:id,name,agenda_id,specialty_usd as specialty'
             ]
        ])
        //->whereRaw("start_date = '".$request->date."'")
        ->get() 
        : AgendaCategory::select('agenda_categories.id','name_es as name')->with([
             'agenda:id,agenda_category_id,start_date,subject_pen as subject' => [
                 'speakers:id,name,agenda_id,specialty_pen as specialty'
             ]
        ])
        //->whereRaw("start_date = '".$request->date."'")
        ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'agenda' => $agenda
            ]
        ]);
    }

    public function get_trips (Request $request, User $user) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en'
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $trip = ($request->language == 'es') ? Trip::select('id','user_id','type_es as type','airline','departure_time','arrival_time','flying_number','driver')->where('user_id',$user->id)->get() : Trip::select('id','user_id','type_en as type','airline','departure_time','arrival_time','flying_number','driver')->where('user_id',$user->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'trips' => $trip
            ]
        ]);
    }

    public function get_tours (Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en'
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $tours = ($request->language == 'es') ? Tour::select('id','title_es as title','description_es as description')->first() : Tour::select('id','title_en as title','description_en as description')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tours' => $tours
            ]
        ]);
    }

    public function get_recommendation_categories (Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en'
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}
        
        $recommendations = ($request->language == 'es') ? RecomendationCategory::select('id','category_es as category','image')->get() : RecomendationCategory::select('id','category_en as category','image')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendations
            ]
        ]);
    }

    public function get_recommendations (Request $request, RecomendationCategory $recommendation_category) {
        return response()->json([
            'success' => true,
            'data' => [
                'recommendations' => $recommendation_category->recomendations
            ]
        ]);
    }

    public function get_destinies (Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en'
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}
        
        $destinies = ($request->language == 'es') ? Destiny::select('id','description_es as description')->first() : Destiny::select('id','description_en as description')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'destinies' => $destinies
            ]
        ]);
    }

    public function get_connectivity (Request $request) {

        $connectivity = Connectivity::select('id','ssid','password')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'connectivity' => $connectivity
            ]
        ]);
    }

    public function upload_media(Request $request) {
        $val = Validator::make($request->all(), [
            'file' => 'required|file',
            'type' => 'required|in:foto,video'
        ]);
        if($val->fails()) return response()->json($val->messages());

        if($request->hasFile('file')){
            $file = $request->file('file');
            //$path = 'public/loreal/files/'.$request->type;
            $path = 'public/loreal/files/'.$request->type;

            try {
                $extension = strrpos($file->getClientOriginalName(), ".")? (Str::substr($file->getClientOriginalName(), strrpos($file->getClientOriginalName(), ".") , Str::length($file->getClientOriginalName()) -strrpos($file->getClientOriginalName(), ".") +1)): "";
                
                $now = Carbon::now();
                $filename = md5($now->toDateTimeString().$file->getClientOriginalName()).$extension;
            } catch (\Exception $e) {
                $filename = $file->getClientOriginalName();
            }

            try {
                $s3 = Storage::disk('s3')->putFileAs($path, $file, $filename, 'public');

                /*return response()->json([
                    'success' => true,
                    'data' => [
                        $s3 
                    ]
                ]);
*/
                $insert = Media::create([
                    'document_type' => $request->type,
                    'url' => env('AWS_URL').$path."/".$filename,
                    'user_id' => auth()->user()->id
                ]);

            } catch (\Exception $e) {
                // Registrando el el log los datos ingresados
                logger('ERROR: archivo adjunto: DailyOperationsController@upload_voucher', ["error" => $e]);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'Archivo agregado'
                ]
            ]);

        } else{
            return response()->json([
                'success' => false,
                'errors' => 'Error en el archivo adjunto',
            ]);
        }
    }


    public function get_media(Request $request) {
        $media = array(
            'photos' => Media::select('id','url','user_id')->where('document_type','foto')->orderByDesc('id')->get(),
            'videos' => Media::select('id','url','user_id')->where('document_type','video')->orderByDesc('id')->get()
        );

        Media::get();

        return response()->json([
            'success' => true,
            'data' => [
                'media' => $media
            ]
        ]);
    }

    public function save_survey (Request $request) {
        $validator = Validator::make($request->all(), [
            'question_1' => 'required|string',
            'question_2' => 'required|string',
            'question_3' => 'required|string',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $survey = Survey::where('user_id', auth()->user()->id)->get();

        if($survey->count() > 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'La encuesta ya ha sido respondida'
                ]
            ]);
        }

        $result = Survey::create([
            'question_1' => $request->question_1,
            'question_2' => $request->question_2,
            'question_3' => $request->question_3,
            'user_id' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Encuesta guardada exitosamente'
            ]
        ]);
    }

    public function get_studies(Request $request) {

        $studies = Study::select('id','name','url')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'studies' => $studies
            ]
        ]);
    }

    public function music_list(Request $request) {

        $music = Music::select('id','song_name')
            ->selectRaw(" if((select 1 from music_votes where music_votes.music_id = music.id and music_votes.user_id = ". auth()->user()->id ." ) = 1 , 1, 0) as voted")
            ->get();

        $pending_votes = MusicVote::where('user_id', auth()->user()->id)->get();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_votes' => 5 - $pending_votes->count(),
                'music' => $music
            ]
        ]);
    }

    public function music_vote(Request $request) {
        $validator = Validator::make($request->all(), [
            'music_id' => 'required|exists:music,id',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $vote = MusicVote::where('user_id', auth()->user()->id)->where('music_id',$request->music_id)->get();

        if($vote->count() > 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'La canción ya ha sido votada'
                ]
            ]);
        }

        $pending_votes = MusicVote::where('user_id', auth()->user()->id)->get();

        if(5 - $pending_votes->count() == 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'Ya realizó todos los votos disponibles'
                ]
            ]);
        }

        $result = MusicVote::create([
            'music_id' => $request->music_id,
            'user_id' => auth()->user()->id
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Voto guardado exitosamente'
            ]
        ]);
    }

    public function get_clima(Request $request) {
        $data = Storage::get('clima.json'); 
        $jsonData = json_decode($data, true);

        return response()->json([
            'success' => true,
            'data' => [
                'clima' => $jsonData
            ]
        ]);

    }

    public function get_transporte(Request $request) {
        $data = Storage::get('transporte.json'); 
        $jsonData = json_decode($data, true);

        return response()->json([
            'success' => true,
            'data' => [
                'transport' => $jsonData
            ]
        ]);

    }

    public function survey (Request $request) {
        $validator = Validator::make($request->all(), [
            'question_1' => 'required|numeric',
            'question_2' => 'required|numeric',
            'question_3' => 'required|numeric',
            'question_4' => 'required|numeric',
            'question_5' => 'required|numeric',
            'question_6' => 'required|numeric',
            'question_7' => 'required|numeric',
            'question_8' => 'required|numeric',
            'question_9' => 'required|string',
            'question_10' => 'required|string',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        if(isset(auth()->user()->id)){
            $survey = Survey::where('user_id', auth()->user()->id)->get();

            if($survey->count() > 0){
                return response()->json([
                    'success' => false,
                    'data' => [
                        'La encuesta ya ha sido respondida'
                    ]
                ]);
            }

            $result = FinalSurvey::create([
                'question_1' => $request->question_1,
                'question_2' => $request->question_2,
                'question_3' => $request->question_3,
                'question_4' => $request->question_4,
                'question_5' => $request->question_5,
                'question_6' => $request->question_6,
                'question_7' => $request->question_7,
                'question_8' => $request->question_8,
                'question_9' => $request->question_9,
                'question_10' => $request->question_10,
                'user_id' => auth()->user()->id
            ]);
        } 
            
        else{
            $result = FinalSurvey::create([
                'question_1' => $request->question_1,
                'question_2' => $request->question_2,
                'question_3' => $request->question_3,
                'question_4' => $request->question_4,
                'question_5' => $request->question_5,
                'question_6' => $request->question_6,
                'question_7' => $request->question_7,
                'question_8' => $request->question_8,
                'question_9' => $request->question_9,
                'question_10' => $request->question_10
            ]);
        }

        

        return response()->json([
            'success' => true,
            'data' => [
                'Encuesta guardada exitosamente'
            ]
        ]);
    }
}
