<?php

namespace App\Http\Controllers\Admin\Executives;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Supervisors;

class ReportsController extends Controller
{
    //
    public function new_clients(Request $request) {

        $executive_id = auth()->id();

        $request['executive_id'] = $executive_id;

        $consult = new Supervisors\ReportsController();
        $result = $consult->new_clients($request)->getData();

        return $result;
    }

    public function monthly_sales(Request $request) {

        $executive_id = auth()->id();

        $request['executive_id'] = $executive_id;

        $consult = new Supervisors\ReportsController();
        $result = $consult->monthly_sales($request)->getData();

        return $result;
    }
}
