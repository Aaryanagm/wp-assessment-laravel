<?php

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
use App\Http\Controllers\Web\AuthWebController;

Route::get('/login', [AuthWebController::class, 'showLogin']);
Route::post('/login', [AuthWebController::class, 'login']);
Route::get('/logout', [AuthWebController::class, 'logout']);

Route::get('/dashboard', function () {
    return view('dashboard');
});
Route::get('/', function () {
    return view('welcome');
});
