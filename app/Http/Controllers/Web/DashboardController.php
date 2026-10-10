<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalTenants    = Tenant::count();
        $activeTenants   = Tenant::where('is_active', true)->count();
        $recentTenants   = Tenant::latest()->limit(6)->get();

        return view('dashboard', compact('totalTenants', 'activeTenants', 'recentTenants'));
    }
}
