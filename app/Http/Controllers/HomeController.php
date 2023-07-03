<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index(): View
    {
        $links = DB::table('links')->orderByDesc('created_at')->limit(10)->get();
        return view('app', compact('links'));
    }
}
