<?php

use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Document\DocumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;


Route::get('/', [CompanyController::class , 'index']);
Route::post('save/document', [DocumentController::class , 'upload']);
Route::post('document/{id}/preview', [DocumentController::class , 'show']);


Route::post('/replace-pdf', [DocumentController::class , 'replacePdf']);




// In your routes/api.php
Route::get('/documents/view/{filename}', function ($filename) {
    $path = storage_path('app/private/' . $filename);

    if (!file_exists($path)) {
        abort(404);
    }

    // Add any authorization logic here if needed

    return response()->file($path);
});



Route::get('/{any}', function () {
    return Inertia::render('Company/Layout/Layout');
})->where('any', '.*');
