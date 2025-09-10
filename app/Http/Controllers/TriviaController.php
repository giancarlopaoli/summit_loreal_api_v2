<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use App\Models\TriviaQuestion;
use App\Models\TriviaOption;
use App\Models\TriviaResult;
use Illuminate\Http\Request;

class TriviaController extends Controller
{
    //
    public function question_list (Request $request) {
        $validator = Validator::make($request->all(), [
            'language' => 'required|in:es,en',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        $trivia = ($request->language == 'es') ? TriviaQuestion::select('id','question_es as question', 'subject_es as subject','speaker_id','status')->with(['trivia_options:id,trivia_question_id,question_es as question,correct'])
            ->with(['speaker:id,name'])->where('status','Activa')->first() : 
            TriviaQuestion::select('id','question_es as question', 'subject_en as subject','speaker_id','status')->with(['trivia_options:id,trivia_question_id,question_en as question,correct'])
            ->with(['speaker:id,name'])->where('status','Activa')->first();

        if(!is_null($trivia)){
            $result = TriviaResult::where('user_id', auth()->user()->id)->where('trivia_question_id', $trivia->id)->get();

            if($result->count() > 0){
                return response()->json([
                    'success' => false,
                    'data' => null
                ]);
            }
        }
            
        return response()->json([
            'success' => true,
            'data' => [
                'trivia' => $trivia
            ]
        ]);
    }

    public function send_result (Request $request, TriviaQuestion $trivia_question) {
        $validator = Validator::make($request->all(), [
            'option' => 'required|exists:trivia_options,id',
        ]);

        if($validator->fails()) {return response()->json(['success' => false,'errors' => $validator->errors()->toJson()]);}

        if($trivia_question->status != 'Activa'){
            return response()->json([
                'success' => false,
                'data' => [
                    'La pregunta ya no se encuentra activa'
                ]
            ]);
        }

        $result = TriviaResult::where('user_id', auth()->user()->id)->where('trivia_question_id', $trivia_question->id)->get();

        if($result->count() > 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'La pregunta ya ha sido respondida'
                ]
            ]);
        }

        $option = TriviaOption::where('id', $request->option)->where('trivia_question_id', $trivia_question->id)->get();

        if($option->count() == 0){
            return response()->json([
                'success' => false,
                'data' => [
                    'Opción invalida'
                ]
            ]);
        }

        $result = TriviaResult::create([
            'trivia_question_id' => $trivia_question->id,
            'user_id' => auth()->user()->id,
            'trivia_option_id' => $request->option
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'Respuesta guardada exitosamente'
            ]
        ]);
    }
}
