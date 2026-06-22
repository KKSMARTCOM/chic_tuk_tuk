<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Driver;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class DriversImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $imported = 0;
    public int $skipped  = 0;
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            $line = $index + 2; // +2 car ligne 1 = en-têtes

            try {
                // Vérifier les champs obligatoires
                if (empty($row['nom']) || empty($row['email']) || empty($row['telephone'])) {
                    $this->errors[] = "Ligne {$line} : nom, email et téléphone sont obligatoires.";
                    $this->skipped++;
                    continue;
                }

                // Éviter les doublons
                if (User::where('email', $row['email'])->exists()) {
                    $this->errors[] = "Ligne {$line} : email {$row['email']} déjà utilisé — ignoré.";
                    $this->skipped++;
                    continue;
                }

                // Créer l'utilisateur
                $user = User::create([
                    'name'     => trim($row['nom']),
                    'email'    => strtolower(trim($row['email'])),
                    'phone'    => trim($row['telephone']),
                    'password' => Hash::make($row['mot_de_passe'] ?? 'ChicTukTuk@2025'),
                    'profil'     => 'driver',
                    'is_active' => strtolower($row['statut_compte'] ?? 'actif') === 'actif',
                ]);

                // Créer le profil conducteur
                Driver::create([
                    'user_id'        => $user->id,
                    'license_number' => trim($row['n_permis'] ?? ''),
                    'vehicle_number' => trim($row['n_vehicule'] ?? ''),
                    'vehicle_type'   => trim($row['type_vehicule'] ?? 'tricycle'),
                    'is_available'   => strtolower($row['disponibilite'] ?? 'disponible') === 'disponible',
                    'total_trips'    => (int) ($row['total_courses'] ?? 0),
                ]);

                $this->imported++;
            } catch (\Exception $e) {
                $this->errors[] = "Ligne {$line} : " . $e->getMessage();
                $this->skipped++;
            }
        }
    }
}
