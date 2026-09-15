<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Zone extends Model
{
    use HasUuid, Notifiable, HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'is_active'
    ];

    public function pricesFrom()
    {
        return $this->hasMany(Pricing::class, 'from_zone');
    }

    public function pricesTo()
    {
        return $this->hasMany(Pricing::class, 'to_zone');
    }
}
