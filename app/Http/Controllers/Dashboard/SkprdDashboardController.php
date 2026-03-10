<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SkprdDashboardController extends Controller
{
    public function index()
    {
        return view('skprd.index', [
            'tgl_dari'   => now()->startOfYear()->toDateString(),
            'tgl_sampai' => now()->endOfYear()->toDateString(),
        ]);
    }
}
