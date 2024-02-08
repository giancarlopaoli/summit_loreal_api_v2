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

    Route::POST('forgot-password', [\App\Http\Controllers\Clients\AuthController::class, 'forgot_password']);
    Route::GET('res/instruction/{operation}', [\App\Http\Controllers\Admin\AdminController::class, 'instruction']);
    Route::GET('res/download-document-operation', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'download_file']);
    Route::GET('res/download-document-register/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'download_document']);
    Route::POST('res/datatec', [\App\Http\Controllers\Admin\DatatecController::class, 'datatec_exchange_rate']);

    Route::POST('res/calculadora', [\App\Http\Controllers\Admin\DatatecController::class, 'calculadora']);
    Route::GET('res/tcambio', [\App\Http\Controllers\Admin\DatatecController::class, 'tipocambio']);

    Route::POST('res/confirmop', [\App\Http\Controllers\Admin\Operations\WsCorfidController::class, 'confirm_operation_corfid']);//->middleware('corfidws');

    Route::POST('produccion', [\App\Http\Controllers\Admin\AdminController::class, 'pase_a_produccion']);

    Route::middleware('auth:sanctum','role:cliente','validate_client_user')->group(function () {
        Route::GET('/me', function(Request $request) {
            return auth()->user();
        });

        Route::prefix('dashboard')->group(function () {
            Route::GET('indicators', [\App\Http\Controllers\Clients\DashboardController::class, 'get_indicators']);
            Route::GET('graphs', [\App\Http\Controllers\Clients\DashboardController::class, 'graphs']);
            Route::GET('exchange-rate', [\App\Http\Controllers\Clients\DashboardController::class, 'exchange_rate']);
            Route::GET('negotiated-operations', [\App\Http\Controllers\Clients\DashboardController::class, 'number_negotiated_operations']);
        });

        Route::prefix('immediate-operation')->group(function () {
            Route::GET('minimum-amount', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'get_minimum_amount']);
            Route::GET('quote', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'quote_operation']);
            Route::GET('validate-coupon', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'validate_coupon']);
            Route::POST('', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'create_operation']);
            Route::PUT('assign-analyst', [\App\Http\Controllers\Clients\InmediateOperationController::class, 'assign_analyst_to_operation']);
        });

        Route::prefix('my-operations')->group(function () {
            Route::GET('list', [\App\Http\Controllers\Clients\MyOperationsController::class, 'list_my_operations']);
            Route::GET('download-file', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'download_file']);
            
            Route::GET('{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'operation_detail']);
            Route::DELETE('cancel/{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'cancel_operation']);
            Route::POST('upload-voucher', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'upload_voucher']);
            Route::GET('instruction/{operation}', [\App\Http\Controllers\Admin\AdminController::class, 'instruction']);
        });


        Route::prefix('my-bank-accounts')->group(function () {
            Route::POST('', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'new_account']);
            Route::GET('list', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'list_accounts']);
            Route::DELETE('{account_id}', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'delete_account']);
            Route::POST('{account_id}/main', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'set_main_account']);
            Route::PUT('{account_id}', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'edit_bank_account']);
        });

        Route::prefix('profile')->group(function () {
            Route::GET('detail', [\App\Http\Controllers\Clients\ProfileController::class, 'profile_detail']);
            Route::PUT('edit-user', [\App\Http\Controllers\Clients\ProfileController::class, 'edit_user']);
            Route::PUT('edit-client', [\App\Http\Controllers\Clients\ProfileController::class, 'edit_client']);
            Route::GET('clients-list', [\App\Http\Controllers\Clients\ProfileController::class, 'clients_list']);
            Route::GET('users', [\App\Http\Controllers\Clients\ProfileController::class, 'users_list']);
            Route::POST('change', [\App\Http\Controllers\Clients\ProfileController::class, 'change']);
            Route::GET('bank-accounts', [\App\Http\Controllers\Clients\ProfileController::class, 'bank_accounts']);
            Route::POST('users', [\App\Http\Controllers\Clients\ProfileController::class, 'add_user']);
            Route::DELETE('users', [\App\Http\Controllers\Clients\ProfileController::class, 'delete_user']);
            Route::PUT('change-password', [\App\Http\Controllers\Clients\ProfileController::class, 'change_password']);

        });

        Route::prefix('interbank-operation')->group(function () {
            Route::GET('minimum-amount', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_minimum_amount']);
            Route::GET('escrow-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_escrow_accounts']);
            Route::GET('bank-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_client_bank_accounts']);
            Route::GET('quote', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'quote_operation']);
            Route::POST('create', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'create_operation']);
        });

        Route::prefix('negotiated-operation')->group(function () {
            Route::GET('max-hour', [\App\Http\Controllers\Clients\NegotiatedOperationController::class, 'max_hour']);
            Route::GET('quote', [\App\Http\Controllers\Clients\NegotiatedOperationController::class, 'quote_operation']);
            Route::POST('', [\App\Http\Controllers\Clients\NegotiatedOperationController::class, 'create_operation']);
            Route::GET('', [\App\Http\Controllers\Clients\NegotiatedOperationController::class, 'operations_list']);
            Route::GET('{operation}', [\App\Http\Controllers\Clients\NegotiatedOperationController::class, 'operation_detail']);
            Route::POST('{operation}', [\App\Http\Controllers\Clients\NegotiatedOperationController::class, 'accept_operation']);
        });

        Route::prefix('indicators')->group(function () {
            Route::GET('', [\App\Http\Controllers\Clients\IndicatorsController::class, 'indicators']);
        });

        Route::prefix('alerts')->group(function () {
            Route::GET('', [\App\Http\Controllers\Clients\AlertsController::class, 'alerts_list']);
            Route::POST('', [\App\Http\Controllers\Clients\AlertsController::class, 'new_alert']);
            Route::DELETE('{exchange_rate_alert}', [\App\Http\Controllers\Clients\AlertsController::class, 'delete_alert']);
        });

        Route::prefix('tables')->group(function () {
            Route::GET('currencies', [\App\Http\Controllers\Admin\MasterTablesController::class, 'currencies']);
            Route::GET('banks', [\App\Http\Controllers\Admin\MasterTablesController::class, 'banks']);
            Route::GET('account-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'account_types']);
            Route::GET('escrow-accounts', [\App\Http\Controllers\Admin\MasterTablesController::class, 'escrow_accounts']);
            Route::GET('document-types', [\App\Http\Controllers\Register\RegisterController::class, 'document_types']);
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

        Route::GET('instruction/{operation}', [\App\Http\Controllers\Admin\AdminController::class, 'instruction']);

        ########## General admin  #############
        Route::GET('has-permission', [\App\Http\Controllers\Admin\AdminController::class, 'has_permission']);
        Route::GET('has-role', [\App\Http\Controllers\Admin\AdminController::class, 'has_role']);

        Route::GET('person-document-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'person_document_types']);
        Route::GET('associate-document-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'associate_document_types']);
        Route::GET('clients-document-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'clients_document_types']);
        Route::GET('roles', [\App\Http\Controllers\Admin\MasterTablesController::class, 'roles']);
        Route::GET('contact-type', [\App\Http\Controllers\Admin\MasterTablesController::class, 'lead_contact_type']);
        Route::GET('regions', [\App\Http\Controllers\Admin\MasterTablesController::class, 'regions']);
        Route::GET('sectors', [\App\Http\Controllers\Admin\MasterTablesController::class, 'sectors']);
        Route::GET('economic-activities', [\App\Http\Controllers\Admin\MasterTablesController::class, 'economic_activities']);

        Route::GET('account-types', [\App\Http\Controllers\Admin\MasterTablesController::class, 'account_types']);
        Route::GET('currencies', [\App\Http\Controllers\Admin\MasterTablesController::class, 'currencies']);
        Route::GET('banks', [\App\Http\Controllers\Admin\MasterTablesController::class, 'banks']);
        Route::GET('document-types', [\App\Http\Controllers\Register\RegisterController::class, 'document_types']);

        Route::GET('executives-full-time', [\App\Http\Controllers\Admin\MasterTablesController::class, 'executives_full_time']);

        Route::GET('exchange-rate', [\App\Http\Controllers\Admin\DatatecController::class, 'exchange_rate']);

        Route::GET('vendor-spreads', [\App\Http\Controllers\Admin\Vendors\ReportsController::class, 'vendor_spreads']);

        ########## Módulo de Operaciones  #############
        Route::prefix('operations')->middleware('role:operaciones')->group(function () {
            Route::GET('daily-operations', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'daily_operations']);
            Route::GET('detail/{operation}', [\App\Http\Controllers\Clients\MyOperationsController::class, 'operation_detail']);
            Route::GET('vendor-list', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'vendor_list']);
            Route::GET('operation-statuses', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'operation_statuses']);
            
            Route::middleware('permission:modificar_estado_operacion')->group(function () {
                Route::PUT('status/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'change_status']);
            });

            Route::POST('match/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'match_operation']);
            Route::PUT('cancel/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'cancel']);
            Route::PUT('confirm-funds/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'confirm_funds']);
            Route::POST('upload-voucher', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'upload_voucher']);
            Route::POST('document', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'upload_document']);
            
            Route::PUT('to-pending-funds/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'to_pending_funds']);
            Route::POST('voucher-vendor-instruction/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'voucher_vendor_instruction']);
            Route::POST('vendor-instruction/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'vendor_instruction']);
            Route::POST('invoice/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'invoice']);
            Route::POST('invoice-mail/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'invoice_email']);
            Route::PUT('close/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'close_operation']);

            Route::middleware('permission:editar_operacion')->group(function () {
                Route::PUT('update/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'update']);
                Route::PUT('update-escrow-accounts/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'update_escrow_accounts']);
                Route::PUT('update-client-accounts/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'update_client_accounts']);
            });

            Route::GET('escrow-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_escrow_accounts']);
            Route::GET('bank-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_client_bank_accounts']);

            Route::GET('download-file', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'download_file']);

            Route::GET('operation-analysts', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'operation_analyst']);
            Route::PUT('operation-analysts', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'analyst_status']);
            Route::GET('operation-analyst-summary', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'operation_analyst_summary']);

            ########## Tiempos de Atención #############
            Route::prefix('times')->group(function () {
                Route::GET('daily-times', [\App\Http\Controllers\Admin\Operations\OperationsTimesController::class, 'daily_times']);
            });

            ########## WS CORFID  #############
            Route::prefix('wscorfid')->group(function () {
                Route::POST('register-operation/{operation}', [\App\Http\Controllers\Admin\Operations\WsCorfidController::class, 'register_operation']);
                Route::POST('register-client/{client}', [\App\Http\Controllers\Admin\Operations\WsCorfidController::class, 'register_client']);
            });

            ########## Operaciones contravalor recaudado  #############
            Route::prefix('countervalue')->middleware('permission:firmar_operaciones')->group(function () {
                Route::GET('list', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'countervalue_list']);
                Route::POST('sign/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'operation_sign']);
                Route::DELETE('document/{operation_document}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'delete_document']);
            });


            ########## Gestión Analistas de Operaciones  #############
            Route::prefix('analysts')->middleware('permission:analista_operaciones')->group(function () {
                Route::PUT('{operation}/assign-analyst', [\App\Http\Controllers\Admin\Operations\OperationsAnalystsController::class, 'assign_analyst_to_operation']);

                Route::GET('', [\App\Http\Controllers\Admin\Operations\OperationsAnalystsController::class, 'analysts_list']);
                Route::GET('users', [\App\Http\Controllers\Admin\Operations\OperationsAnalystsController::class, 'users_list']);
                Route::POST('', [\App\Http\Controllers\Admin\Operations\OperationsAnalystsController::class, 'add_analyst']);
                Route::PUT('{operations_analyst}', [\App\Http\Controllers\Admin\Operations\OperationsAnalystsController::class, 'edit_analyst']);
                Route::GET('history', [\App\Http\Controllers\Admin\Operations\OperationsAnalystsController::class, 'analysts_history']);
            });


            ########## Administración de usuarios  #############
            Route::prefix('users')->group(function () {
                Route::GET('list', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'list']);
                Route::GET('detail/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'detail']);
                Route::PUT('edit/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'edit']);
                Route::PUT('deactivate/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'deactivate']);
                Route::PUT('activate/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'activate']);
                Route::PUT('unblock/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'unblock']);
                Route::POST('reset-password/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'reset_password']);
                Route::GET('client-list/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'client_list']);
                Route::GET('clients/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'clients']);
                Route::POST('attach-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'attach_client']);
                Route::DELETE('detach-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'detach_client']);
                Route::PUT('assign-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'assign_client']);
                Route::PUT('activate-client/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'activate_client']);
                
                Route::group(['middleware' => ['permission:editar_roles']], function () {
                    Route::GET('roles/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'roles']);
                    Route::PUT('roles/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'save_roles']);
                });

                Route::group(['middleware' => ['permission:editar_permisos']], function () {
                    Route::GET('permissions/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'permissions']);
                    Route::PUT('permissions/{user}', [\App\Http\Controllers\Admin\Operations\UsersController::class, 'save_permissions']);
                });

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
                Route::GET('bank-account/receipt/{bank_account_receipt}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'download_bank_account_receipt']);
                Route::PUT('bank-account/approve/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'approve_bank_account']);
                Route::PUT('bank-account/reject/{bank_account}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'reject_bank_account']);
                
                Route::GET('{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'detail']);
                Route::PUT('{client}/accountable-email', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'edit_accountable_email']);

                
                Route::GET('user/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'users']);
                Route::GET('assigned-users/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'assigned_users']);
                Route::POST('user/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'attach_user']);
                Route::DELETE('user/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'detach_user']);
                

                Route::PUT('evaluation/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'evaluation'])->middleware('permission:aprobar_clientes');

                Route::GET('comission/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'comission_list']);
                Route::POST('comission/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'create_comission']);
                Route::DELETE('comission/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'delete_comission']);


                Route::middleware('permission:editar_cliente')->group(function () {
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
                        
                });
                
            });

            ########## Administración de Tipo de Cambio  #############
            Route::prefix('exchange-rate')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Operations\ExchangeRateController::class, 'list']);
                Route::POST('', [\App\Http\Controllers\Admin\DatatecController::class, 'new_exchange_rate']);
                Route::DELETE('{exchange_rate}', [\App\Http\Controllers\Admin\Operations\ExchangeRateController::class, 'delete'])->middleware('permission:eliminar_tipocambio');
            });

            ########## Tiempos de Atención #############
            Route::prefix('times')->group(function () {
                Route::GET('dashboard', [\App\Http\Controllers\Admin\Operations\OperationsTimesController::class, 'dashboard']);
                Route::GET('daily-times', [\App\Http\Controllers\Admin\Operations\OperationsTimesController::class, 'daily_times']);
            });

            ########## Administración de Rangos de operación  #############
            Route::prefix('ranges')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'list']);
                Route::PUT('{range}', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'edit']);
                Route::PUT('active/{range}', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'active']);
                Route::GET('itbc', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'list_itbc']);
                Route::PUT('itbc/{itbc_range}', [\App\Http\Controllers\Admin\Operations\RangesController::class, 'edit_itbc']);
            });

            ########## Ejecutivos Comerciales  #############
            Route::prefix('executives')->group(function () {
                Route::GET('comissions', [\App\Http\Controllers\Admin\Operations\ExecutivesController::class, 'comissions']);
                Route::GET('comissions/{executive}', [\App\Http\Controllers\Admin\Operations\ExecutivesController::class, 'comission_detail']);
            });

            ########## Administración de Configuraciones  #############
            Route::prefix('configurations')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Operations\ConfigurationsController::class, 'list']);
                Route::PUT('{configuration}', [\App\Http\Controllers\Admin\Operations\ConfigurationsController::class, 'edit']);
            });

            ########## Reportes  #############
            Route::prefix('reports')->group(function () {
                Route::GET('selling-buying', [\App\Http\Controllers\Admin\Operations\ReportsController::class, 'selling_buying_report']);
                Route::GET('corfid', [\App\Http\Controllers\Admin\Operations\ReportsController::class, 'corfid']);
            });

        });

        ########## Módulo de Proveedores de Liquidez  #############
        Route::prefix('vendors')->middleware('role:proveedor')->group(function () {

            ########## Dashboard  #############
            Route::prefix('dashboard')->group(function () {
                Route::GET('vendors', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'vendors']);
                Route::GET('indicators', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'indicators']);
                Route::PUT('{configuration}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'edit']);

                Route::GET('spreads', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'spreads']);
                Route::PUT('spreads/{vendor_spread}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'edit_spread']);
                Route::DELETE('spreads/{vendor_spread}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'delete_spread']);
                Route::POST('spreads', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'register_spreads']);
                Route::DELETE('spreads', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'delete_all_spreads']);

                Route::GET('ranges', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'ranges']);
                Route::PUT('ranges/{vendor_range}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'edit_price']);

                Route::GET('avaliable-operations', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'avaliable_operations']);
                Route::GET('operation/{operation}', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'operation_detail']);
                Route::POST('operation/{operation}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'match_operation']);
                Route::GET('operations-in-progress', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'operations_in_progress']);
                
                Route::GET('report', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'report']);


                Route::GET('test', [\App\Http\Controllers\Admin\Vendors\DashboardController::class, 'test']);

            });

            ########## Administración de Rangos  #############
            Route::prefix('ranges')->group(function () {
                Route::DELETE('{vendor_range}', [\App\Http\Controllers\Admin\Vendors\RangesController::class, 'delete_range']);
                Route::POST('', [\App\Http\Controllers\Admin\Vendors\RangesController::class, 'register_range']);

            });

            ########## Administración de Cuentas bancarias  #############
            Route::prefix('bank-accounts')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Vendors\BankAccountsController::class, 'bank_accounts']);
                Route::PUT('{bank_account}', [\App\Http\Controllers\Admin\Vendors\BankAccountsController::class, 'update_bank_account']);

            });

            ########## Reportes  #############
            Route::prefix('reports')->group(function () {
                Route::GET('operations', [\App\Http\Controllers\Admin\Vendors\ReportsController::class, 'operations']);

            });
        });


        ########## Módulo de Ejecutivos  #############
        Route::prefix('executives')->middleware('role:ejecutivos')->group(function () {

            ########## dashboard  #############
            Route::prefix('dashboard')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Executives\DashboardController::class, 'dashboard']);
                Route::GET('daily-times', [\App\Http\Controllers\Admin\Operations\OperationsTimesController::class, 'daily_times']);
                Route::GET('goal-progress', [\App\Http\Controllers\Admin\Executives\DashboardController::class, 'goal_progress']);
            });

            ########## Leads  #############
            Route::prefix('leads')->group(function () {
                Route::GET('exists-company', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'exists_company']);
                Route::GET('exists-person', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'exists_person']);
                Route::POST('', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'register_lead']);
                Route::GET('statuses', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'statuses']);
                Route::GET('tracking-phases', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'tracking_phases']);
                Route::GET('tracking-forms', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'tracking_forms']);
                Route::GET('tracking-statuses', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'tracking_statuses']);
                Route::GET('', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'list']);
                Route::GET('{lead}', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'lead_detail']);
                Route::POST('{lead}/contact', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'new_contact']);
                Route::POST('{lead}/follow', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'new_follow']);
                Route::GET('contact/{lead_contact}', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'contact_detail']);
                Route::PUT('contact/{lead_contact}', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'edit_contact']);
                Route::DELETE('contact/{lead_contact}', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'delete_contact']);
                Route::POST('contact/{lead_contact}/data', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'new_contact_data']);
                Route::DELETE('contact-data/{contact_data}', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'delete_contact_data']);
                Route::PUT('{lead}', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'edit_lead']);
                Route::GET('{lead}/validate-ruc', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'validate_ruc']);
                Route::GET('{lead}/validate-dni', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'validate_dni']);

            });

            ########## clients  #############
            Route::prefix('clients')->group(function () {

                Route::GET('', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'list']);
                Route::GET('base', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'clients_base']);
                Route::GET('tracking-forms', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'tracking_forms']);
                Route::GET('tracking-statuses', [\App\Http\Controllers\Admin\Executives\LeadsController::class, 'tracking_statuses']);
                Route::GET('financial-vendors', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'vendors']);
                Route::GET('no-escrow-vendors', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'no_escrow_vendors']);
                Route::GET('escrow-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_escrow_accounts']);
                Route::GET('{client}/follow', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'client_follows']);
                Route::POST('{client}/follow', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'register_follow']);
                Route::GET('bank-accounts', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'get_client_bank_accounts']);
                Route::GET('quote-inmediate', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'quote_inmediate_operation']);
                Route::POST('create-inmediate', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'create_inmediate_operation']);

                Route::GET('quote-interbank', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'quote_operation']);
                Route::POST('interbank-parameters', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'interbank_parameters']);
                Route::GET('interbank-parameters', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'get_interbank_parameters']);
                Route::DELETE('interbank-parameters/{ibops_client_comissions}', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'delete_interbank_parameter']);
                Route::POST('create-interbank', [\App\Http\Controllers\Clients\InterbankOperationController::class, 'create_operation']);
                
                Route::POST('operation/upload-voucher/{operation_id}', [\App\Http\Controllers\Admin\Operations\DailyOperationsController::class, 'upload_voucher']);

                Route::POST('bank-accounts', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'new_account']);
                Route::GET('my-bank-accounts', [\App\Http\Controllers\Clients\MyBankAccountsController::class, 'list_accounts']);

                Route::PUT('{client}/user', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'update_user']);
                Route::GET('{client}', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'client_detail']);
            });


            ########## Comisiones  #############
            Route::prefix('comissions')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Executives\ClientsController::class, 'comissions']);
            });

            ########## Reports  #############
            Route::prefix('reports')->group(function () {
                Route::GET('new-clients', [\App\Http\Controllers\Admin\Executives\ReportsController::class, 'new_clients']);
                Route::GET('monthly-sales', [\App\Http\Controllers\Admin\Executives\ReportsController::class, 'monthly_sales']);
            });

            Route::GET('exchange-rate', [\App\Http\Controllers\Admin\Operations\ExchangeRateController::class, 'list']);

        });

        ########## Módulo de Supervidores  #############
        Route::prefix('supervisors')->middleware('role:supervisores')->group(function () {
            
            ########## dashboard  #############
            Route::prefix('dashboard')->group(function () {
                Route::GET('', [\App\Http\Controllers\Admin\Supervisors\DashboardController::class, 'dashboard']);
                Route::GET('sales-progress', [\App\Http\Controllers\Admin\Supervisors\DashboardController::class, 'sales_progress']);
            });

            ########## Reports  #############
            Route::prefix('reports')->group(function () {
                Route::GET('new-clients', [\App\Http\Controllers\Admin\Supervisors\ReportsController::class, 'new_clients']);
                Route::GET('monthly-sales', [\App\Http\Controllers\Admin\Supervisors\ReportsController::class, 'monthly_sales']);
            });

        });

        ########## Módulo CORFID  #############
        Route::prefix('corfid')->middleware('role:corfid')->group(function () {

            Route::GET('clients-list', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'list']);
            Route::GET('client/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'detail']);
            Route::PUT('evaluation/{client}', [\App\Http\Controllers\Admin\Operations\ClientsController::class, 'evaluation']);

        });


    });

    Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
        return $request->user();
    });


});

