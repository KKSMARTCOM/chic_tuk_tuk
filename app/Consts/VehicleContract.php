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

    // Charge fiscale
    public const TAXE = [
        24 => 241,
        30 => 229,
        36 => 211
    ];

    public const AMOUNTS = [
        24 => 6112,
        30 => 5691,
        36 => 5251
    ];

    // Charges mensuelles par défaut
    public const DEFAULT_UNLIMITED_INTERNET   = 5_000;
    public const DEFAULT_SPOTIFY_PREMIUM      = 2_500;
    public const DEFAULT_MANAGER_REMUNERATION = 20_000;
}
