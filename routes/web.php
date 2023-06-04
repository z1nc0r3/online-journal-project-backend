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

    Route::group(['prefix' => '/update/'], function () {
        Route::post('/trainee', [UserController::class, 'updateTrainee']);
        Route::post('/supervisor', [UserController::class, 'updateSupervisor']);
        Route::post('/evaluator', [UserController::class, 'updateEvaluator']);
    });

    Route::group(['prefix' => '/delete/'], function () {
        Route::post('/trainee', [UserController::class, 'deleteTrainee']);
        Route::post('/supervisor', [UserController::class, 'deleteSupervisor']);
        Route::post('/evaluator', [UserController::class, 'deleteEvaluator']);
    });

    Route::group(['prefix' => '/get/'], function () {
        Route::get('/trainee/{id}', [UserController::class, 'getTrainee']);
        Route::get('/supervisor/{id}', [UserController::class, 'getSupervisor']);
        Route::get('/evaluator/{id}', [UserController::class, 'getEvaluator']);
    });
});
