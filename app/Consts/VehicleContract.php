<?php

// ============================================================
// app/Consts/VehicleContract.php
// ============================================================

namespace App\Consts;

class VehicleContract
{
    // Montants totaux par durée de contrat (FCFA)
    public const TOTAL_AMOUNTS = [
        24 => 3_100_000,
        30 => 3_604_872,
        36 => 4_049_100,
    ];

    // Charges mensuelles par défaut
    public const DEFAULT_UNLIMITED_INTERNET   = 5_000;
    public const DEFAULT_SPOTIFY_PREMIUM      = 2_500;
    public const DEFAULT_MANAGER_REMUNERATION = 20_000;
}
