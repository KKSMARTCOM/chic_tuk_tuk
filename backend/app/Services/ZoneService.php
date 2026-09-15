<?php

namespace App\Services;

use App\Models\Zone;

class ZoneService
{
    public function getZones()
    {
        return Zone::all();
    }
}
