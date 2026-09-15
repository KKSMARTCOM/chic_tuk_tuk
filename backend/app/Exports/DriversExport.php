<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriversExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    WithStyles,
    ShouldAutoSize
{
    public function collection()
    {
        return User::where('profil', 'driver')
            ->with('driver')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Nom',
            'Email',
            'Téléphone',
            'N° Permis',
            'N° Véhicule',
            'Type Véhicule',
            'Statut Compte',
            'Disponibilité',
            'Total Courses',
            'Date Inscription',
        ];
    }

    public function map($user): array
    {
        return [
            $user->name,
            $user->email,
            $user->phone,
            $user->driver?->license_number ?? '',
            $user->driver?->vehicle_number ?? '',
            $user->driver?->vehicle_type   ?? '',
            $user->is_active ? 'Actif' : 'Inactif',
            $user->driver?->is_available ? 'Disponible' : 'Indisponible',
            $user->driver?->total_trips ?? 0,
            $user->created_at->format('d/m/Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // En-têtes en gras avec fond
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF7C3AED']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
