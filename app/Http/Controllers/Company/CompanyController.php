<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CompanyController extends Controller
{

    public function index()
    {
        $companyWithEmployees = Company::with('employees')->first();
        $companyDocuments = Document::where('company_id' , 1)->get();
        return Inertia::render('Company/Layout/Layout', [
            'company' => $companyWithEmployees,
            'documents'=>$companyDocuments,
        ]);
    }
}
