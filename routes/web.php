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


Route::get('/secure-pdf/{filename}', function ($filename) {
    // Ensure the file exists in the private documents folder
    $path = $filename;

    if (!Storage::exists("private/$path")) {
        abort(404);
    }

    // Return the PDF with the correct response headers
    return response()->file(storage_path("app/private/$path"), [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="' . basename($path) . '"',  // Ensures the PDF opens in browser
    ]);
});



Route::get('/{any}', function () {
    return Inertia::render('Company/Layout/Layout');
})->where('any', '.*');
