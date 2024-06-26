<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Carbon;

class Sla extends Pivot
{

    protected $table = 'sla';
    protected $fillable = [
        'ticket_id',
    ];




    public function detail(): BelongsTo
    {
        return $this->belongsTo(slamanagement::class, 'sla_id', 'id');
    }

}