<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AccountType;
use App\Models\Bank;

class MasterTablesController extends Controller
{
    public function banks() {
        return response()->json([
            'success' => true,
            'data' => Bank::where('active', true)->get()
        ]);
    }

    public function account_types() {
        return response()->json([
            'success' => true,
            'data' => AccountType::where('active', true)->get()
        ]);
    }
}
