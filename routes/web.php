<?php

use App\Http\Controllers\Company\CompanyController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;


Route::get('/', [CompanyController::class , 'index']);



Route::get('/{any}', function () {
    return Inertia::render('Company/Layout/Layout');
})->where('any', '.*');
