<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasUuid, HasFactory;

    protected $fillable = [
        'driver_id',
        'dates',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'dates' => 'array',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
