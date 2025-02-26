<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'email',
        'company_id',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
