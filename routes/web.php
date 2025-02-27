<?php

use App\Http\Controllers\Company\CompanyController;
use App\Http\Controllers\Document\DocumentController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', [CompanyController::class , 'index']);
Route::post('save/document', [DocumentController::class , 'upload']);
Route::post('document/{id}/preview', [DocumentController::class , 'show']);



Route::get('/{any}', function () {
    return Inertia::render('Company/Layout/Layout');
})->where('any', '.*');
