<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\JournalRecordsController;

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

    // Create a new user
    Route::group(['prefix' => '/create/'], function () {
        Route::post('/trainee', [UserController::class, 'createTrainee']);
        Route::post('/supervisor', [UserController::class, 'createSupervisor']);
        Route::post('/evaluator', [UserController::class, 'createEvaluator']);
        Route::post('/bulk', [UserController::class, 'createBulkUsers']);
    });

    // Update an existing user
    Route::group(['prefix' => '/update/'], function () {
        Route::post('/trainee/{id}', [UserController::class, 'updateTrainee']);
        Route::post('/supervisor/{id}', [UserController::class, 'updateSupervisor']);
        Route::post('/evaluator/{id}', [UserController::class, 'updateEvaluator']);
    });

    // Delete an existing user
    Route::post('/delete/{id}', [UserController::class, 'deleteUser']);

    // Reset user password
    Route::post('/reset/password/{id}', [UserController::class, 'resetPassword']);

    // Assign a supervisor and evaluator to a trainee
    Route::post('/update/assign', [UserController::class, 'assignSupervisorAndEvaluator']);

    // Get routes
    Route::group(['prefix' => '/get/'], function () {
        // Get a list of users
        Route::get('/trainee/list', [UserController::class, 'getTraineeList']);
        Route::get('/supervisor/list', [UserController::class, 'getSupervisorList']);
        Route::get('/evaluator/list', [UserController::class, 'getEvaluatorList']);

        // Get user details
        Route::get('/trainee/{id}', [UserController::class, 'getTraineeDetails']);
        Route::get('/supervisor/{id}', [UserController::class, 'getSupervisorDetails']);
        Route::get('/evaluator/{id}', [UserController::class, 'getEvaluatorDetails']);

        // Get trainee records
        Route::group(['prefix' => '/record/'], function () {
            Route::get('/currentMonth/week/{trainee_id}', [JournalRecordsController::class, 'getCurrentMonthRecords']);
            Route::get('/week/{trainee_id}', [JournalRecordsController::class, 'getAllTraineeRecords']);
            Route::get('/all/{supervisor_id}', [JournalRecordsController::class, 'getAllTraineeRecordsForSupervisor']);
        });
    });

    // Set routes
    Route::group(['prefix' => '/set/'], function () {
        Route::group(['prefix' => '/record/'], function () {
            Route::post('/week', [JournalRecordsController::class, 'createRecord']);
        });
    });

});
