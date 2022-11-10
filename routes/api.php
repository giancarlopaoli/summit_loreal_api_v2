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

    ###############################
    ###### Módulo de clientes #####
    ###############################

    Route::post('login', [\App\Http\Controllers\Clients\AuthController::class, 'login']);
    Route::post('logout', [\App\Http\Controllers\Clients\AuthController::class, 'logout']);

    Route::middleware('auth:sanctum','role:cliente','validate_client_user')->group(function () {
        Route::get('/me', function(Request $request) {
            return auth()->user();
        });

        Route::prefix('dashboard')->group(function () {
            Route::get('indicators', [\App\Http\Controllers\Clients\DashboardController::class, 'get_indicators']);
            Route::get('graphs', [\App\Http\Controllers\Clients\DashboardController::class, 'graphs']);
            Route::get('exchange-rate', [\App\Http\Controllers\Clients\DashboardController::class, 'exchange_rate']);
        });

        Route::prefix('immediate-operation')->group(function () {
            Route::get('minimum-amount', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'get_minimum_amount']);
            Route::get('quote', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'quote_operation']);
            Route::get('validate-coupon', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'validate_coupon']);
            Route::post('', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'create_operation']);
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
            Route::put('edit_user', [\App\Http\Controllers\Clients\ProfileController::class, 'edit_user']);
            Route::put('edit_client', [\App\Http\Controllers\Clients\ProfileController::class, 'edit_client']);
            Route::get('clients_list', [\App\Http\Controllers\Clients\ProfileController::class, 'clients_list']);
            Route::get('users', [\App\Http\Controllers\Clients\ProfileController::class, 'users_list']);
            Route::post('change', [\App\Http\Controllers\Clients\ProfileController::class, 'change']);
            Route::get('bank-accounts', [\App\Http\Controllers\Clients\ProfileController::class, 'bank_accounts']);
            Route::post('users', [\App\Http\Controllers\Clients\ProfileController::class, 'add_user']);
            Route::delete('users', [\App\Http\Controllers\Clients\ProfileController::class, 'delete_user']);

        });

        Route::prefix('interbank-operation')->group(function () {
            Route::get('minimum-amount', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_minimum_amount']);
            Route::get('escrow-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_escrow_accounts']);
            Route::get('bank-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_client_bank_accounts']);
            Route::get('quote', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'quote_operation']);
            Route::post('create', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'create_operation']);
        });

        Route::prefix('tables')->group(function () {
            Route::get('banks', [\App\Http\Controllers\Admin\MasterTablesController::class, 'banks']);
            Route::get('account-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'account_types']);
            Route::get('escrow-accounts', [\App\Http\Controllers\Admin\MasterTablesController::class, 'escrow_accounts']);
        });

        Route::prefix('datatec')->group(function () {
            Route::post('', [\App\Http\Controllers\Admin\DatatecController::class, 'new_exchange_rate']);
        });
    });

    ########################################
    ###### Registro de nuevos clientes #####
    ########################################

    Route::prefix('register')->group(function () {
        Route::get('document-types', [\App\Http\Controllers\Register\RegisterController::class, 'document_types']);
        Route::get('representatives-document-types', [\App\Http\Controllers\Register\RegisterController::class, 'representatives_document_types']);
        Route::get('banks', [\App\Http\Controllers\Register\RegisterController::class, 'bank_list']);
        Route::get('economic-activities', [\App\Http\Controllers\Register\RegisterController::class, 'economic_activities']);
        Route::get('account-types', [\App\Http\Controllers\Register\RegisterController::class, 'account_types']);

        Route::get('departments', [\App\Http\Controllers\Register\RegisterController::class, 'departments']);
        Route::get('provinces', [\App\Http\Controllers\Register\RegisterController::class, 'provinces']);
        Route::get('districts', [\App\Http\Controllers\Register\RegisterController::class, 'districts']);
        Route::get('countries', [\App\Http\Controllers\Register\RegisterController::class, 'countries']);
        Route::get('professions', [\App\Http\Controllers\Register\RegisterController::class, 'professions']);
        Route::post('ficha-ruc', [\App\Http\Controllers\Register\FicharucController::class, 'ficha_ruc']);

        Route::get('validate-dni', [\App\Http\Controllers\Register\RegisterController::class, 'validate_dni']);
        Route::get('validate-ruc', [\App\Http\Controllers\Register\RegisterController::class, 'validate_ruc']);
        Route::get('exists-person', [\App\Http\Controllers\Register\RegisterController::class, 'exists_person']);
        Route::get('exists-company', [\App\Http\Controllers\Register\RegisterController::class, 'exists_company']);

        Route::post('register-person', [\App\Http\Controllers\Register\RegisterController::class, 'register_person']);
        Route::post('register-company', [\App\Http\Controllers\Register\RegisterController::class, 'register_company']);
        Route::post('upload-file', [\App\Http\Controllers\Register\RegisterController::class, 'upload_file']);

        
    });
    

    #####################################
    ###### Módulo de Administración #####
    #####################################

    Route::post('admin/login', [\App\Http\Controllers\Admin\AdminController::class, 'login']);

    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {

        ########## General admin  #############
        Route::get('has-permission', [\App\Http\Controllers\Admin\AdminController::class, 'has_permission']);
        Route::get('has-role', [\App\Http\Controllers\Admin\AdminController::class, 'has_role']);


        ########## Módulo de Operaciones  #############
        Route::prefix('operations')->middleware('role:operaciones')->group(function () {
            Route::get('daily-operations', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'daily_operations']);
            Route::get('detail/{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'operation_detail']);
            Route::get('vendor-list', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'vendor_list']);
            Route::post('match/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'match_operation']);
            Route::put('cancel/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'cancel']);
            Route::put('confirm-funds/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'confirm_funds']);
            Route::post('upload-voucher', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'upload_voucher']);
            Route::put('to-pending-funds/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'to_pending_funds']);
            Route::post('invoice/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'invoice']);
            Route::put('close/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'close_operation']);

            Route::get('download-file', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'download_file']);

            ########## Operaciones contravalor recaudado  #############
            Route::prefix('countervalue')->group(function () {
                Route::get('list', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'countervalue_list']);
                Route::post('sign/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'operation_sign']);

                
            });

        });

    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

