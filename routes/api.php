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

    Route::POST('login', [\App\Http\Controllers\Clients\AuthController::class, 'login']);
    Route::POST('logout', [\App\Http\Controllers\Clients\AuthController::class, 'logout']);

    Route::middleware('auth:sanctum','role:cliente','validate_client_user')->group(function () {
        Route::GET('/me', function(Request $request) {
            return auth()->user();
        });

        Route::prefix('dashboard')->group(function () {
            Route::GET('indicators', [\App\Http\Controllers\Clients\DashboardController::class, 'get_indicators']);
            Route::GET('graphs', [\App\Http\Controllers\Clients\DashboardController::class, 'graphs']);
            Route::GET('exchange-rate', [\App\Http\Controllers\Clients\DashboardController::class, 'exchange_rate']);
        });

        Route::prefix('immediate-operation')->group(function () {
            Route::GET('minimum-amount', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'get_minimum_amount']);
            Route::GET('quote', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'quote_operation']);
            Route::GET('validate-coupon', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'validate_coupon']);
            Route::POST('', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'create_operation']);
        });

        Route::prefix('my-operations')->group(function () {
            Route::GET('list', [\App\Http\Controllers\Clients\MyOperationsController::class, 'list_my_operations']);
            Route::GET('{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'operation_detail']);
        });

        Route::prefix('my-bank-accounts')->group(function () {
            Route::POST('', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'new_account']);
            Route::GET('list', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'list_accounts']);
            Route::DELETE('{account_id}', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'delete_account']);
            Route::POST('{account_id}/main', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'set_main_account']);
        });

        Route::prefix('profile')->group(function () {
            Route::GET('detail', [\App\Http\Controllers\Clients\ProfileController::class, 'profile_detail']);
            Route::PUT('edit_user', [\App\Http\Controllers\Clients\ProfileController::class, 'edit_user']);
            Route::PUT('edit_client', [\App\Http\Controllers\Clients\ProfileController::class, 'edit_client']);
            Route::GET('clients_list', [\App\Http\Controllers\Clients\ProfileController::class, 'clients_list']);
            Route::GET('users', [\App\Http\Controllers\Clients\ProfileController::class, 'users_list']);
            Route::POST('change', [\App\Http\Controllers\Clients\ProfileController::class, 'change']);
            Route::GET('bank-accounts', [\App\Http\Controllers\Clients\ProfileController::class, 'bank_accounts']);
            Route::POST('users', [\App\Http\Controllers\Clients\ProfileController::class, 'add_user']);
            Route::DELETE('users', [\App\Http\Controllers\Clients\ProfileController::class, 'delete_user']);

        });

        Route::prefix('interbank-operation')->group(function () {
            Route::GET('minimum-amount', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_minimum_amount']);
            Route::GET('escrow-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_escrow_accounts']);
            Route::GET('bank-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_client_bank_accounts']);
            Route::GET('quote', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'quote_operation']);
            Route::POST('create', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'create_operation']);
        });

        Route::prefix('tables')->group(function () {
            Route::GET('banks', [\App\Http\Controllers\Admin\MasterTablesController::class, 'banks']);
            Route::GET('account-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'account_types']);
            Route::GET('escrow-accounts', [\App\Http\Controllers\Admin\MasterTablesController::class, 'escrow_accounts']);
        });

        Route::prefix('datatec')->group(function () {
            Route::POST('', [\App\Http\Controllers\Admin\DatatecController::class, 'new_exchange_rate']);
        });
    });

    ########################################
    ###### Registro de nuevos clientes #####
    ########################################

    Route::prefix('register')->group(function () {
        Route::GET('document-types', [\App\Http\Controllers\Register\RegisterController::class, 'document_types']);
        Route::GET('representatives-document-types', [\App\Http\Controllers\Register\RegisterController::class, 'representatives_document_types']);
        Route::GET('banks', [\App\Http\Controllers\Register\RegisterController::class, 'bank_list']);
        Route::GET('economic-activities', [\App\Http\Controllers\Register\RegisterController::class, 'economic_activities']);
        Route::GET('account-types', [\App\Http\Controllers\Register\RegisterController::class, 'account_types']);

        Route::GET('departments', [\App\Http\Controllers\Register\RegisterController::class, 'departments']);
        Route::GET('provinces', [\App\Http\Controllers\Register\RegisterController::class, 'provinces']);
        Route::GET('districts', [\App\Http\Controllers\Register\RegisterController::class, 'districts']);
        Route::GET('countries', [\App\Http\Controllers\Register\RegisterController::class, 'countries']);
        Route::GET('professions', [\App\Http\Controllers\Register\RegisterController::class, 'professions']);
        Route::POST('ficha-ruc', [\App\Http\Controllers\Register\FicharucController::class, 'ficha_ruc']);

        Route::GET('validate-dni', [\App\Http\Controllers\Register\RegisterController::class, 'validate_dni']);
        Route::GET('validate-ruc', [\App\Http\Controllers\Register\RegisterController::class, 'validate_ruc']);
        Route::GET('exists-person', [\App\Http\Controllers\Register\RegisterController::class, 'exists_person']);
        Route::GET('exists-company', [\App\Http\Controllers\Register\RegisterController::class, 'exists_company']);

        Route::POST('register-person', [\App\Http\Controllers\Register\RegisterController::class, 'register_person']);
        Route::POST('register-company', [\App\Http\Controllers\Register\RegisterController::class, 'register_company']);
        Route::POST('upload-file', [\App\Http\Controllers\Register\RegisterController::class, 'upload_file']);

        
    });
    

    #####################################
    ###### Módulo de Administración #####
    #####################################

    Route::POST('admin/login', [\App\Http\Controllers\Admin\AdminController::class, 'login']);

    Route::prefix('admin')->middleware('auth:sanctum')->group(function () {

        ########## General admin  #############
        Route::GET('has-permission', [\App\Http\Controllers\Admin\AdminController::class, 'has_permission']);
        Route::GET('has-role', [\App\Http\Controllers\Admin\AdminController::class, 'has_role']);

        Route::GET('person-document-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'person_document_types']);
        Route::GET('associate-document-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'associate_document_types']);
        Route::GET('roles', [\App\Http\Controllers\Admin\MasterTablesController::class, 'roles']);

        Route::GET('account-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'account_types']);
        Route::GET('currencies', [\App\Http\Controllers\Admin\MasterTablesController::class, 'currencies']);


        ########## Módulo de Operaciones  #############
        Route::prefix('operations')->middleware('role:operaciones')->group(function () {
            Route::GET('daily-operations', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'daily_operations']);
            Route::GET('detail/{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'operation_detail']);
            Route::GET('vendor-list', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'vendor_list']);
            Route::POST('match/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'match_operation']);
            Route::PUT('cancel/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'cancel']);
            Route::PUT('confirm-funds/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'confirm_funds']);
            Route::POST('upload-voucher', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'upload_voucher']);
            Route::PUT('to-pending-funds/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'to_pending_funds']);
            Route::POST('vendor-instruction/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'vendor_instruction']);
            Route::POST('invoice/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'invoice']);
            Route::PUT('close/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'close_operation']);

            Route::PUT('update/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'update']);
            Route::PUT('update-escrow-accounts/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'update_escrow_accounts']);
            Route::PUT('update-client-accounts/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'update_client_accounts']);
            Route::GET('escrow-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_escrow_accounts']);
            Route::GET('bank-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_client_bank_accounts']);
            

            Route::GET('download-file', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'download_file']);

            ########## Operaciones contravalor recaudado  #############
            Route::prefix('countervalue')->group(function () {
                Route::GET('list', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'countervalue_list']);
                Route::POST('sign/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'operation_sign']);
                
            });

            ########## Administración de usuarios  #############
            Route::prefix('users')->group(function () {
                Route::GET('list', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'list']);
                Route::GET('detail/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'detail']);
                Route::PUT('edit/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'edit']);
                Route::PUT('deactivate/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'deactivate']);
                Route::PUT('activate/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'activate']);
                Route::POST('reset-password/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'reset_password']);
                Route::GET('client-list/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'client_list']);
                Route::GET('clients/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'clients']);
                Route::POST('attach-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'attach_client']);
                Route::DELETE('detach-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'detach_client']);
                Route::PUT('assign-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'assign_client']);
                Route::PUT('activate-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'activate_client']);
                Route::GET('roles/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'roles']);
                Route::PUT('roles/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'save_roles']);
                Route::POST('change-password/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'change_password']);

                Route::GET('mail-exists', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'mails_exists']);
                Route::POST('new', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'new']);

            });


            ########## Administración de clientes  #############
            Route::prefix('clients')->group(function () {
                Route::GET('list', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'list']);
                Route::GET('bank-accounts/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'bank_account_list']);
                Route::PUT('bank-account/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'edit_bank_account']);
                Route::POST('bank-account/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'add_bank_account']);
                Route::DELETE('bank-account/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'delete_bank_account']);
                Route::POST('bank-account/receipt/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'upload_bank_account_receipt']);
                Route::PUT('bank-account/approve/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'approve_bank_account']);
                Route::PUT('bank-account/reject/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'reject_bank_account']);
                
                Route::GET('{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'detail']);
                Route::PUT('{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'edit']);

                Route::DELETE('document/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'delete_document']);
                Route::POST('document/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'upload_document']);
                Route::GET('document/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'download_document']);
                
                Route::DELETE('associate/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'delete_associate']);
                Route::POST('associate/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'add_associate']);
                Route::PUT('associate/{representative}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'edit_associate']);

                Route::DELETE('representative/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'delete_representative']);
                Route::POST('representative/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'add_representative']);
                Route::PUT('representative/{representative}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'edit_representative']);

                Route::DELETE('approve/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'approve_client']);
                Route::DELETE('reject/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'reject_client']);

                Route::GET('user/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'users']);
                Route::POST('user/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'attach_user']);
                Route::DELETE('user/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'detach_user']);
                
                Route::PUT('evaluation/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'evaluation']);

                Route::GET('comission/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'comission_list']);
                Route::POST('comission/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'create_comission']);
                Route::DELETE('comission/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'delete_comission']);
                
            });

             ########## Administración de Tipo de Cambio  #############
            Route::prefix('exchange-rate')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Operations\ExchangeRateController::class, 'list']);
                Route::POST('', [\App\Http\Controllers\Admin\DatatecController::class, 'new_exchange_rate']);
                Route::DELETE('{exchange_rate}', [\App\Http\Controllers\Admin\Operations\ExchangeRateController::class, 'delete'])->middleware('permission:eliminar_tipocambio');
            });

            ########## Administración de Rangos de operación  #############
            Route::prefix('ranges')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'list']);
                Route::PUT('{range}', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'edit']);
                Route::PUT('active/{range}', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'active']);

                Route::GET('itbc', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'list_itbc']);
                Route::PUT('itbc/{itbc_range}', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'edit_itbc']);
            });

            ########## Administración de Configuraciones  #############
            Route::prefix('configurations')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Operations\ConfigurationsController::class, 'list']);
                Route::PUT('{configuration}', [\App\Http\Controllers\Admin\Operations\ConfigurationsController::class, 'edit']);
            });

        });

        ########## Módulo de Operaciones  #############
        Route::prefix('vendors')->middleware('role:proveedor')->group(function () {

            ########## Dashboard  #############
            Route::prefix('dashboard')->group(function () {
                Route::GET('vendors', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'vendors']);
                Route::GET('indicators', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'indicators']);
                Route::PUT('{configuration}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'edit']);

                Route::GET('spreads', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'spreads']);
                Route::PUT('spreads/{vendor_spread}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'edit_spread']);
                Route::DELETE('spreads/{vendor_spread}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'delete_spread']);



                Route::GET('ranges', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'ranges']);
                Route::PUT('ranges/{vendor_range}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'edit_price']);

            });
        });

    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });
});

