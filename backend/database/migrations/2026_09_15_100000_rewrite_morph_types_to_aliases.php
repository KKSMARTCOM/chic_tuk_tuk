<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Réécrit les FQCN stockés dans les colonnes polymorphes en alias stables.
 *
 * Prérequis de la migration vers une architecture DDD : tant que la base référence
 * "App\Models\User", déplacer la classe invalide tous les rôles, permissions et
 * tokens Sanctum. Les alias sont déclarés dans AppServiceProvider::MORPH_MAP.
 *
 * ⚠️ Un rollback d'image Docker seul ne suffit PAS à revenir en arrière : il faut
 * d'abord `php artisan migrate:rollback --step=1`, puis redéployer l'image précédente.
 */
return new class extends Migration
{
    /** Colonnes stockant un type de modèle. */
    private array $columns = [
        ['model_has_roles',        'model_type'],
        ['model_has_permissions',  'model_type'],
        ['personal_access_tokens', 'tokenable_type'],
    ];

    /** FQCN historique => alias cible (doit refléter AppServiceProvider::MORPH_MAP). */
    private array $aliases = [
        'App\Models\Booking'         => 'booking',
        'App\Models\Commission'      => 'commission',
        'App\Models\Driver'          => 'driver',
        'App\Models\DriverContract'  => 'driver_contract',
        'App\Models\FcmToken'        => 'fcm_token',
        'App\Models\LeaveRequest'    => 'leave_request',
        'App\Models\Notification'    => 'notification',
        'App\Models\Payment'         => 'payment',
        'App\Models\Permission'      => 'permission',
        'App\Models\Pricing'         => 'pricing',
        'App\Models\PromoCode'       => 'promo_code',
        'App\Models\Role'            => 'role',
        'App\Models\Testimonial'     => 'testimonial',
        'App\Models\TouristCircuit'  => 'tourist_circuit',
        'App\Models\User'            => 'user',
        'App\Models\Vehicle'         => 'vehicle',
        'App\Models\VehicleContract' => 'vehicle_contract',
        'App\Models\VehiclePause'    => 'vehicle_pause',
        'App\Models\Zone'            => 'zone',
    ];

    public function up(): void
    {
        $this->rewrite($this->aliases);
    }

    public function down(): void
    {
        $this->rewrite(array_flip($this->aliases));
    }

    /**
     * Applique la table de correspondance à toutes les colonnes polymorphes.
     * Idempotent : ne touche que les lignes portant encore l'ancienne valeur.
     */
    private function rewrite(array $map): void
    {
        foreach ($this->columns as [$table, $column]) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            foreach ($map as $from => $to) {
                $updated = DB::table($table)->where($column, $from)->update([$column => $to]);

                if ($updated > 0) {
                    echo "    {$table}.{$column} : {$updated} ligne(s) « {$from} » → « {$to} »" . PHP_EOL;
                }
            }
        }
    }
};
