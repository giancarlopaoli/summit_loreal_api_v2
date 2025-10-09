<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::POST('login', [\App\Http\Controllers\AuthController::class, 'login'])->name('login');
Route::POST('logout', [\App\Http\Controllers\AuthController::class, 'logout']);
Route::POST('forgot-password', [\App\Http\Controllers\AuthController::class, 'forgot_password']);

Route::prefix('register')->group(function () {

    ################# Registro ###############
    Route::POST('', [\App\Http\Controllers\RegisterController::class, 'register']);
    Route::POST('massive', [\App\Http\Controllers\RegisterController::class, 'massive_register']);
    Route::POST('image', [\App\Http\Controllers\RegisterController::class, 'image_format']);
    Route::GET('countries', [\App\Http\Controllers\RegisterController::class, 'countries']);
    Route::GET('test', [\App\Http\Controllers\RegisterController::class, 'test']);
});

Route::POST('res/survey', [\App\Http\Controllers\DashboardController::class, 'survey']);

Route::middleware('auth:sanctum')->group(function () {

    Route::PUT('change-password', [\App\Http\Controllers\DashboardController::class, 'change_password']);
    Route::GET('profile', [\App\Http\Controllers\DashboardController::class, 'get_profile']);
    Route::POST('profile/update', [\App\Http\Controllers\DashboardController::class, 'update_profile']);


    ################# Dashboard ###############
    Route::GET('speakers', [\App\Http\Controllers\DashboardController::class, 'get_speakers']);
    Route::GET('speakers/{speaker}', [\App\Http\Controllers\DashboardController::class, 'speaker_detail']);

    Route::GET('agenda', [\App\Http\Controllers\DashboardController::class, 'get_agenda']);
    Route::GET('trips/{user}', [\App\Http\Controllers\DashboardController::class, 'get_trips']);
    Route::GET('tours', [\App\Http\Controllers\DashboardController::class, 'get_tours']);
    Route::GET('recommendations', [\App\Http\Controllers\DashboardController::class, 'get_recommendation_categories']);
    Route::GET('recommendations/{recommendation_category}', [\App\Http\Controllers\DashboardController::class, 'get_recommendations']);
    Route::GET('destinies', [\App\Http\Controllers\DashboardController::class, 'get_destinies']);
    Route::GET('connectivity', [\App\Http\Controllers\DashboardController::class, 'get_connectivity']);

    Route::POST('upload-media', [\App\Http\Controllers\DashboardController::class, 'upload_media']);
    Route::GET('media', [\App\Http\Controllers\DashboardController::class, 'get_media']);

    Route::GET('survey', [\App\Http\Controllers\DashboardController::class, 'save_survey']);
    Route::POST('finalsurvey', [\App\Http\Controllers\DashboardController::class, 'survey']);

    Route::GET('studies', [\App\Http\Controllers\DashboardController::class, 'get_studies']);

    Route::GET('clima', [\App\Http\Controllers\DashboardController::class, 'get_clima']);


    ################# Trivia ###############
    Route::prefix('trivia')->group(function () {
        Route::GET('question', [\App\Http\Controllers\TriviaController::class, 'question_list']);
        Route::POST('question/{trivia_question}', [\App\Http\Controllers\TriviaController::class, 'send_result']);
    });

    ################# Orquesta ###############
    Route::prefix('music')->group(function () {
        Route::GET('list', [\App\Http\Controllers\DashboardController::class, 'music_list']);
        Route::POST('vote', [\App\Http\Controllers\DashboardController::class, 'music_vote']);
    });


});


Route::prefix('admin')->group(function () {
    Route::POST('login', [\App\Http\Controllers\Administration\AuthController::class, 'login']);
    Route::POST('logout', [\App\Http\Controllers\Administration\AuthController::class, 'logout']);

    Route::middleware('auth:sanctum')->group(function () {
        ################# Usuarios ###############
        Route::prefix('users')->group(function () {
            Route::GET('', [\App\Http\Controllers\Administration\UsersController::class, 'list']);
            Route::GET('mailing', [\App\Http\Controllers\Administration\UsersController::class, 'mailing']);
            Route::POST('', [\App\Http\Controllers\RegisterController::class, 'register']);

        });

        ################# Otras funciones ###############
        Route::GET('speakers', [\App\Http\Controllers\Administration\GeneralController::class, 'get_speakers']);
        Route::GET('tour', [\App\Http\Controllers\Administration\GeneralController::class, 'get_tours']);
        Route::GET('destinies', [\App\Http\Controllers\Administration\GeneralController::class, 'get_destinies']);
        Route::GET('connectivity', [\App\Http\Controllers\Administration\GeneralController::class, 'get_connectivity']);
        Route::GET('recommendations_categories', [\App\Http\Controllers\Administration\GeneralController::class, 'get_recommendation_categories']);
        Route::GET('recommendations/{recommendation_category}', [\App\Http\Controllers\Administration\GeneralController::class, 'get_recommendations']);

        Route::GET('test', [\App\Http\Controllers\Administration\GeneralController::class, 'test']);

        ################# agenda ###############
        Route::prefix('agenda')->group(function () {
            Route::GET('', [\App\Http\Controllers\Administration\AgendaController::class, 'list']);
            Route::PUT('category/{agenda_category}', [\App\Http\Controllers\Administration\AgendaController::class, 'edit_agenda_category']);
            Route::PUT('', [\App\Http\Controllers\Administration\AgendaController::class, 'edit_agenda']);
            Route::GET('{agenda_speaker}', [\App\Http\Controllers\Administration\AgendaController::class, 'speaker_detail']);

        });

        ################# Trivia ###############
        Route::prefix('trivia')->group(function () {
            Route::GET('questions', [\App\Http\Controllers\Administration\TriviaController::class, 'questions_list']);
            Route::PUT('activate/{trivia_question}', [\App\Http\Controllers\Administration\TriviaController::class, 'activate_question']);
            Route::PUT('deactivate/{trivia_question}', [\App\Http\Controllers\Administration\TriviaController::class, 'deactivate_question']);
            Route::PUT('reset/{trivia_question}', [\App\Http\Controllers\Administration\TriviaController::class, 'reset_question']);
            Route::GET('report/{trivia_question}', [\App\Http\Controllers\Administration\TriviaController::class, 'report']);
        });

        ################# Orquesta ###############
        Route::prefix('music')->group(function () {
            Route::GET('votes', [\App\Http\Controllers\Administration\MusicController::class, 'music_votes']);
            Route::GET('', [\App\Http\Controllers\Administration\MusicController::class, 'music_list']);
            Route::PUT('{music}', [\App\Http\Controllers\Administration\MusicController::class, 'desactivate_music']);
        });
    });
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});