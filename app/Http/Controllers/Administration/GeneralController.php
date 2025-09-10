<?php

namespace App\Http\Controllers\Administration;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Connectivity;
use App\Models\Destiny;
use App\Models\Tour;
use App\Models\Recomendation;
use App\Models\RecomendationCategory;
use App\Models\Speaker;
use App\Events\StartTrivia;

class GeneralController extends Controller
{
    public function get_tours (Request $request) {

        $tours = Tour::select('id','title_es','title_en','description_es','description_en')->first();

        return response()->json([
            'success' => true,
            'data' => [
                'tours' => $tours
            ]
        ]);
    }

    public function get_speakers (Request $request) {
        $speakers = Speaker::select('id','name','english_description','spanish_description','image')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'speakers' => $speakers
            ]
        ]);
    }

    public function get_recommendation_categories (Request $request) {

        $recommendations = RecomendationCategory::select('id','category_es','category_en','image')->get();
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
        $destinies = Destiny::select('id','description_es','description_en')->first();

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

    public function test (Request $request) {

        // Iniciando trivia
        StartTrivia::dispatch();

        return response()->json([
            'success' => true,
            'data' => [
                'trivia iniciada'
            ]
        ]);
    }

    
}
