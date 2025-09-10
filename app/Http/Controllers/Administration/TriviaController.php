<?php

namespace App\Http\Controllers\Administration;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use App\Models\TriviaQuestion;
use App\Models\TriviaOption;
use App\Models\TriviaResult;
use App\Events\StartTrivia;

class TriviaController extends Controller
{
    //
    public function questions_list (Request $request) {

        $trivias = TriviaQuestion::select('id','question_es as question', 'subject_es as subject','speaker_id','status')->with(['trivia_options:id,trivia_question_id,question_es as question,correct'])
            ->with(['speaker:id,name'])->get();

        return response()->json([
            'success' => true,
            'data' => [
                'trivia' => $trivias
            ]
        ]);
    }

    public function activate_question (Request $request, TriviaQuestion $trivia_question) {

        if($trivia_question->status != 'Pendiente'){
            return response()->json([
                'success' => false,
                'data' => [
                    'Solo se puede activar una pregunta en estado Pendiente'
                ]
            ]);
        }

        #### Finalizando preguntas activas antes de activar otra
        TriviaQuestion::where('status','Activa')->update(['status' => 'Finalizada']);

        $trivia_question->status='Activa';
        $trivia_question->save();

        // Iniciando trivia
        StartTrivia::dispatch();

        return response()->json([
            'success' => true,
            'data' => [
                'Pregunta activada exitosamente'
            ]
        ]);
    }

    public function reset_question (Request $request, TriviaQuestion $trivia_question) {

        if($trivia_question->status == 'Pendiente'){
            return response()->json([
                'success' => false,
                'data' => [
                    'Solo se puede reiniciar una pregunta en estado Activo o Finalizado'
                ]
            ]);
        }

        $trivia_question->results()->delete();

        $trivia_question->status='Pendiente';
        $trivia_question->save();

        return response()->json([
            'success' => true,
            'data' => [
                'Pregunta reiniciada exitosamente'
            ]
        ]);
    }

    public function report (Request $request, TriviaQuestion $trivia_question) {

        $report = array(
            'question_name' => $trivia_question->question_en,
            'correct' => TriviaResult::selectRaw("count(trivia_options.correct) as cuenta" )->join('trivia_options','trivia_options.id','=','trivia_results.trivia_option_id')->where('trivia_options.correct',1)->where('trivia_results.trivia_question_id', $trivia_question->id)->first()->cuenta,
            'incorrect' => TriviaResult::selectRaw("count(trivia_options.correct) as cuenta" )->join('trivia_options','trivia_options.id','=','trivia_results.trivia_option_id')->where('trivia_options.correct',0)->where('trivia_results.trivia_question_id', $trivia_question->id)->first()->cuenta,
        );

        return response()->json([
            'success' => true,
            'data' => [
                'report' => $report
            ]
        ]);
    }

    public function deactivate_question (Request $request, TriviaQuestion $trivia_question) {

        if($trivia_question->status != 'Activa'){
            return response()->json([
                'success' => false,
                'data' => [
                    'Solo se puede activar una pregunta en estado Activa'
                ]
            ]);
        }

        $trivia_question->status='Finalizada';
        $trivia_question->save();

        // Iniciando trivia
        StartTrivia::dispatch();

        return response()->json([
            'success' => true,
            'data' => [
                'Pregunta finalizada exitosamente'
            ]
        ]);
    }
}
