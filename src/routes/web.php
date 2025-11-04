<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 一般ユーザー関連
Route::get('/login', [AuthController::class, 'login'])->name('user.auth.login');
Route::get('/register', [AuthController::class, 'register'])->name('user.auth.register');

// 管理者関連
Route::get('/admin/login', [AuthController::class, 'loginAdmin'])->name('admin.auth.login');
