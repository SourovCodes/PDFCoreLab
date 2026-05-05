<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.index', [
            'totalKeys' => $user->apiKeys()->count(),
            'activeKeys' => $user->apiKeys()->active()->count(),
            'totalCompressions' => $user->pdfCompressions()->count(),
            'recentCompressions' => $user->pdfCompressions()->latest()->take(5)->get(),
        ]);
    }
}
