<?php

namespace App\Imports;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class DriversImport implements ToModel, WithHeadingRow, WithValidation
{
    private $successCount = 0;
    private $errors = [];

    public function model(array $row)
    {
        try {
            // Vérifier si l'utilisateur existe déjà par email ou téléphone
            $existingUser = User::where('email', $row['email'] ?? null)
                ->orWhere('phone', $row['telephone'] ?? $row['phone'] ?? null)
                ->first();

            if ($existingUser) {
                throw new \Exception("Un utilisateur avec cet email/téléphone existe déjà");
            }

            $phone = $row['telephone'] ?? $row['phone'] ?? null;
            if (!$phone) {
                throw new \Exception("Le téléphone est requis");
            }

            // Créer l'utilisateur
            $user = User::create([
                'name' => $row['nom'] ?? $row['name'] ?? null,
                'email' => $row['email'] ?? null,
                'phone' => $phone,
                'adresse' => $row['adresse'] ?? $row['address'] ?? null,
                'password' => Hash::make($phone), // Utiliser le téléphone comme mot de passe par défaut
                'role' => 'driver',
                'is_active' => in_array(strtolower($row['actif'] ?? $row['active'] ?? 'oui'), ['oui', 'yes', '1']) ? 1 : 0,
            ]);

            // Créer le profil driver
            $vehicleType = strtolower($row['type_vehicule'] ?? $row['vehicle_type'] ?? 'car');
            if (!in_array($vehicleType, ['moto', 'tricycle', 'car'])) {
                $vehicleType = 'car';
            }

            Driver::create([
                'user_id' => $user->id,
                'license_number' => $row['numero_permis'] ?? $row['license_number'] ?? null,
                'vehicle_number' => $row['numero_vehicule'] ?? $row['vehicle_number'] ?? null,
                'vehicle_type' => $vehicleType,
                'is_available' => in_array(strtolower($row['disponible'] ?? $row['available'] ?? 'oui'), ['oui', 'yes', '1']) ? 1 : 0,
            ]);

            $this->successCount++;

            return $user;
        } catch (\Exception $e) {
            $this->errors[] = [
                'row' => $row['nom'] ?? $row['name'] ?? 'Unknown',
                'error' => $e->getMessage(),
            ];
            return null;
        }
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string|max:255',
            'telephone' => 'required|string|unique:users,phone',
            'email' => 'nullable|email',
            'adresse' => 'nullable|string|max:255',
            'numero_permis' => 'required|string',
            'numero_vehicule' => 'required|string',
            'type_vehicule' => 'required|in:moto,tricycle,car',
            'disponible' => 'nullable|in:Oui,Non,oui,non',
            'actif' => 'nullable|in:Oui,Non,oui,non',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nom.required' => 'Le nom est requis',
            'telephone.required' => 'Le téléphone est requis',
            'telephone.unique' => 'Ce numéro de téléphone existe déjà',
            'email.email' => 'L\'email doit être valide',
            'numero_permis.required' => 'Le numéro de permis est requis',
            'numero_vehicule.required' => 'Le numéro de véhicule est requis',
            'type_vehicule.required' => 'Le type de véhicule est requis',
            'type_vehicule.in' => 'Le type de véhicule doit être: moto, tricycle ou car',
        ];
    }

    public function getSuccessCount()
    {
        return $this->successCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}
