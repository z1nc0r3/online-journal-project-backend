<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
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

Route::group(['prefix' => 'api'], function () {
    Route::post('/login/check', [AuthController::class, 'login'])->name('login');

    Route::group(['prefix' => '/create/'], function () {
        Route::post('/trainee', [UserController::class, 'createTrainee']);
        Route::post('/supervisor', [UserController::class, 'createSupervisor']);
        Route::post('/evaluator', [UserController::class, 'createEvaluator']);
    });
});
