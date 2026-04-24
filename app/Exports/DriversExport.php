<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DriversExport implements FromCollection, WithHeadings, WithStyles
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = User::where('role', 'driver')->with('driver');

        // Appliquer les filtres
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%')
                    ->orWhereHas('driver', function ($driverQuery) use ($search) {
                        $driverQuery->where('license_number', 'like', '%' . $search . '%')
                            ->orWhere('vehicle_number', 'like', '%' . $search . '%');
                    });
            });
        }

        if (isset($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (isset($this->filters['is_available'])) {
            $query->whereHas('driver', function ($q) {
                $q->where('is_available', $this->filters['is_available']);
            });
        }

        $drivers = $query->latest()->get();

        return $drivers->map(function ($driver) {
            return [
                $driver->id,
                $driver->name,
                $driver->email,
                $driver->phone,
                $driver->adresse ?? 'N/A',
                $driver->driver->license_number ?? 'N/A',
                $driver->driver->vehicle_number ?? 'N/A',
                $driver->driver->vehicle_type ?? 'N/A',
                $driver->driver->is_available ? 'Oui' : 'Non',
                $driver->is_active ? 'Oui' : 'Non',
                $driver->driver->total_trips ?? 0,
                $driver->created_at->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nom',
            'Email',
            'Téléphone',
            'Adresse',
            'Numéro Permis',
            'Numéro Véhicule',
            'Type Véhicule',
            'Disponible',
            'Actif',
            'Nombre de Courses',
            'Date de Création',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '8B5CF6'], // Purple-600
                ],
            ],
        ];
    }
}
