<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'company_email',
        'image_url',
        'password',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }
}
