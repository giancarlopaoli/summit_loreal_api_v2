<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\BankAccount;
use App\Models\Client;
use App\Models\ClientStatus;
use App\Models\Country;
use App\Models\District;
use App\Models\DocumentType;
use App\Models\EconomicActivity;
use App\Models\Operation;
use App\Models\Profession;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class PRDSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        //Creating Roles
        Role::create(['name' => 'cliente']);
        Role::create(['name' => 'administrador']);
        Role::create(['name' => 'operaciones']);
        Role::create(['name' => 'proveedor']);
        Role::create(['name' => 'corfid']);
        Role::create(['name' => 'ejecutivos']);
        Role::create(['name' => 'supervisores']);
        
        //Creating Permissions
        Permission::create(['name' => 'firmar_operaciones']);
        Permission::create(['name' => 'editar_roles']);
        Permission::create(['name' => 'aprobar_clientes']);
        Permission::create(['name' => 'editar_cliente']);
        Permission::create(['name' => 'editar_operacion']);
        Permission::create(['name' => 'eliminar_tipocambio']);
        Permission::create(['name' => 'comisiones_ejecutivos']);
        Permission::create(['name' => 'editar_permisos']);
        Permission::create(['name' => 'detracciones']);
        Permission::create(['name' => 'analista_operaciones']);

        $role = Role::findByName('administrador');
        $role->givePermissionTo(Permission::all());
        
    }
}
