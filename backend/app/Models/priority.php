<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class priority extends Model
{
    use HasFactory;
    protected $table = 'priority';

    protected $fillable = [
        'response_time',
        'resolution_time',
        'priority_name'
    ];
}
