<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{

    public function index()
    {
        $companyWithEmployees = Company::with('employees')->first();

        return Inertia::render('Company/Layout/Layout', [
            'company' => $companyWithEmployees,
        ]);
    }
}
