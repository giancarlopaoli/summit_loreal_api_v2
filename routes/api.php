<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('encryptresponses')->group(function () {
    Route::post('login', [\App\Http\Controllers\AuthController::class, 'login']);
    Route::post('login/token', [\App\Http\Controllers\AuthController::class, 'login_token']);
    Route::post('logout', [\App\Http\Controllers\AuthController::class, 'logout']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/me', function(Request $request) {
            return auth()->user();
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('indicators', [\App\Http\Controllers\DashboardController::class, 'get_indicators']);
            Route::get('graphs', [\App\Http\Controllers\DashboardController::class, 'graphs']);
        });

        Route::prefix('immediate_operation')->group(function () {
            Route::get('minimum_amount', [\App\Http\Controllers\InmediateOperationController::class, 'get_minimum_amount']);
        });

        Route::prefix('myOperations')->group(function () {
            Route::get('list', [\App\Http\Controllers\MyOperationsController::class, 'list_my_operations']);
            Route::get('{operation}', [\App\Http\Controllers\MyOperationsController::class, 'operation_detail']);
        });

        Route::prefix('myBankAccounts')->group(function () {
            Route::post('', [\App\Http\Controllers\MyBankAccountsController::class, 'new_account']);
            Route::get('list', [\App\Http\Controllers\MyBankAccountsController::class, 'list_accounts']);
            Route::delete('{account_id}', [\App\Http\Controllers\MyBankAccountsController::class, 'delete_account']);
            Route::post('{account_id}/main', [\App\Http\Controllers\MyBankAccountsController::class, 'set_main_account']);
        });
    });

    Route::prefix('admin')->group(function () {
        Route::prefix('tables')->group(function () {
            Route::get('banks', [\App\Http\Controllers\MasterTablesController::class, 'banks']);
            Route::get('accountTypes', [\App\Http\Controllers\MasterTablesController::class, 'account_types']);
        });
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

