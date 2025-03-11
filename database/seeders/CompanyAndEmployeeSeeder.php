<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CompanyAndEmployeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a company with the given email.
        $company = User::create([
            'name'  => 'Esign',
            'email' => 'hmzah.liaqat@gmail.com',
            'password'      => Hash::make('password'),
        ]);


        Employee::create([
            'user_id' => $company->id,
            'name'       => 'Employee One',
            'email'      => 'employee1@example.com',
        ]);

        // Create the second employee.
        Employee::create([
            'user_id' => $company->id,
            'name'       => 'Employee Two',
            'email'      => 'employee2@example.com',
        ]);


    }
}
