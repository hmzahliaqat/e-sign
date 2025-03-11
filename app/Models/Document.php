<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use setasign\Fpdi\Fpdi;

class Document extends Model
{
    protected $fillable = [
        'title',
        'file_path',
        'pdf_path',
        'page_count',
        'file_type',
        'user_id'
    ];







}
