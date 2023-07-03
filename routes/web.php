<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LinkController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Routes for the React app
Route::get('/', [HomeController::class, 'index']);

// Route for making links shorter
Route::post('/handle-link', [LinkController::class, 'store']);

// Route for redirection to real links
Route::get('/{key}', [LinkController::class, 'redirect']);
