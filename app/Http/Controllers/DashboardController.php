<?php

namespace App\Http\Controllers;
use App\Models\Adsense;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $adsense = Adsense::first();
        return view('super_admin.dashboard', compact('adsense'));
    }
}
