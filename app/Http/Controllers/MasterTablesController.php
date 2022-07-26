<?php

namespace App\Http\Controllers;

use App\Models\AccountType;
use App\Models\Bank;
use Illuminate\Http\Request;

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
