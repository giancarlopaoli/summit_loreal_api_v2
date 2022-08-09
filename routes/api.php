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
    Route::post('login', [\App\Http\Controllers\Clients\AuthController::class, 'login']);
    Route::post('login/token', [\App\Http\Controllers\Clients\AuthController::class, 'login_token']);
    Route::post('logout', [\App\Http\Controllers\Clients\AuthController::class, 'logout']);

    Route::group(['middleware' => ['auth:sanctum']], function () {
        Route::get('/me', function(Request $request) {
            return auth()->user();
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('indicators', [\App\Http\Controllers\Clients\DashboardController::class, 'get_indicators']);
            Route::get('graphs', [\App\Http\Controllers\Clients\DashboardController::class, 'graphs']);
        });

        Route::prefix('immediate-operation')->group(function () {
            Route::get('minimum-amount', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'get_minimum_amount']);
            Route::get('quote', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'quote_operation']);
        });

        Route::prefix('my-operations')->group(function () {
            Route::get('list', [\App\Http\Controllers\Clients\MyOperationsController::class, 'list_my_operations']);
            Route::get('{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'operation_detail']);
        });

        Route::prefix('my-bank-accounts')->group(function () {
            Route::post('', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'new_account']);
            Route::get('list', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'list_accounts']);
            Route::delete('{account_id}', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'delete_account']);
            Route::post('{account_id}/main', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'set_main_account']);
        });

        Route::prefix('profile')->group(function () {
            Route::get('detail', [\App\Http\Controllers\Clients\ProfileController::class, 'profile_detail']);
            Route::get('clients_list', [\App\Http\Controllers\Clients\ProfileController::class, 'clients_list']);
            Route::get('users_list', [\App\Http\Controllers\Clients\ProfileController::class, 'users_list']);
        });
    });

    Route::prefix('admin')->group(function () {
        Route::prefix('tables')->group(function () {
            Route::get('banks', [\App\Http\Controllers\Admin\MasterTablesController::class, 'banks']);
            Route::get('account-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'account_types']);
        });
    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

