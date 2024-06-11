<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subdepartment extends Model
{
    use HasFactory;
    protected $table = 'sub_department';

    protected $fillable = [
        'department_id',
        'name'
    ];

    /**
     * Get the user that owns the Subdepartment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'id');
    }
}
