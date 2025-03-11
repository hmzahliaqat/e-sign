<?php

use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Document\DocumentController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    // return Inertia::render('Welcome', [
    //     'canLogin' => Route::has('login'),
    //     'canRegister' => Route::has('register'),
    //     'laravelVersion' => Application::VERSION,
    //     'phpVersion' => PHP_VERSION,
    // ]);

        return Inertia::render('Auth/Login');

});



// Route::get('/', [CompanyController::class , 'index']);
Route::post('save/document', [DocumentController::class , 'upload']);
Route::post('document/{id}/preview', [DocumentController::class , 'show']);


Route::post('/replace-pdf', [DocumentController::class , 'replacePdf']);




// In your routes/api.php
Route::get('/documents/view/{filename}', function ($filename) {
    $path = storage_path('app/private/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->file($path);
});



Route::get('/{any}', function () {
    return Inertia::render('Company/Layout/Layout');
})->where('any', '.*');






Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
