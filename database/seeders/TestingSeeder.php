<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        User::create([
            'name' => 'admin',
            'last_name' => 'admin',
            'email' => 'email@email.com',
            'document_number' => '12345678',
            'phone' => '987654321',
            'tries' => 0,
            'password' => Hash::make('password'),
            'status' => UserStatus::Activo
        ]);
    }
}
