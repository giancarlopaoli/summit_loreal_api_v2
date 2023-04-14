<?php

namespace Database\Seeders;

use App\Models\Configuration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Configuration::create([
            "shortname" => "IGV",
            "description" => "Porcentage IGV",
            "value" => 18,
        ]);

        Configuration::create([
            "shortname" => "EXPTIME",
            "description" => "Tiempo expiración en minutos",
            "value" => 5,
        ]);

        Configuration::create([
            "shortname" => "OPSSTARTTIME",
            "description" => "Hora inicio operaciones",
            "value" => "9:00",
        ]);

        Configuration::create([
            "shortname" => "OPSENDTIMEPJ",
            "description" => "Hora fin operaciones para PJ",
            "value" => "18:00",
        ]);

        Configuration::create([
            "shortname" => "OPSENDTIMEPN",
            "description" => "Hora fin operaciones para PN",
            "value" => "17:00",
        ]);

        Configuration::create([
            "shortname" => "OPSSTARTDATE",
            "description" => "Dia inicio operaciones",
            "value" => 1,
        ]);

        Configuration::create([
            "shortname" => "OPSENDDATE",
            "description" => "Dia fin operaciones",
            "value" => 5,
        ]);

        Configuration::create([
            "shortname" => "MARKETCLOSE",
            "description" => "Hora de cierre del mercado",
            "value" => "13:30",
        ]);

        Configuration::create([
            "shortname" => "TIMECREATE",
            "description" => "Tiempo de permanencia en crear operación",
            "value" => 180,
        ]);

        Configuration::create([
            "shortname" => "COUPAVAIL",
            "description" => "Disponibilidad de cupones (ALL,PN,PJ)",
            "value" => "ALL",
        ]);

        Configuration::create([
            "shortname" => "MAILSOPS",
            "description" => "Email de envio comprobante de pago billex",
            "value" => "operaciones@billex.pe",
        ]);

        Configuration::create([
            "shortname" => "MAILSCORFID",
            "description" => "Email de envio comprobante de pago corfid",
            "value" => "corfid@billex.pe",
        ]);

        Configuration::create([
            "shortname" => "BTHS",
            "description" => "Tiempo de bloqueo por spread alto en segundos",
            "value" => 40,
        ]);

        Configuration::create([
            "shortname" => "MNTMIN",
            "description" => "Monto minimo requerido para crear una operacion",
            "value" => 1000,
        ]);

        Configuration::create([
            "shortname" => "DETRACTION",
            "description" => "Porcentage Detracción",
            "value" => 12,
        ]);

        Configuration::create([
            "shortname" => "MAXOPPN",
            "description" => "Monto Máximo PN Sin validación",
            "value" => 1000,
        ]);

        Configuration::create([
            "shortname" => "MAXOPPJ",
            "description" => "Monto Máximo PJ Sin validación",
            "value" => 100000,
        ]);

        Configuration::create([
            "shortname" => "OPNEGSENDTIME",
            "description" => "Hora fin operaciones negociadas",
            "value" => "16:00",
        ]);

        Configuration::create([
            "shortname" => "PIPSAVE",
            "description" => "Puntos de ahorro clientes",
            "value" => "250",
        ]);
    }
}
