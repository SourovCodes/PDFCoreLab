<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PdfCompressionController extends Controller
{
    public function index(Request $request): View
    {
        $compressions = $request->user()
            ->pdfCompressions()
            ->latest()
            ->paginate(15);

        return view('dashboard.compressions.index', [
            'compressions' => $compressions,
        ]);
    }
}
