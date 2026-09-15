<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Alias stables des modèles pour les colonnes polymorphes.
     *
     * Ces alias sont écrits en base à la place du FQCN (model_has_roles.model_type,
     * model_has_permissions.model_type, personal_access_tokens.tokenable_type).
     * Ils découplent le schéma du namespace PHP : les classes peuvent être déplacées
     * (migration vers une architecture DDD) sans invalider les rôles, les permissions
     * ni les tokens Sanctum existants.
     *
     * ⚠️ Un alias déjà écrit en base ne doit JAMAIS être renommé ni supprimé sans
     * migration de données correspondante. Cf. la migration
     * 2026_09_15_100000_rewrite_morph_types_to_aliases.
     */
    private const MORPH_MAP = [
        'booking'          => \App\Models\Booking::class,
        'commission'       => \App\Models\Commission::class,
        'driver'           => \App\Models\Driver::class,
        'driver_contract'  => \App\Models\DriverContract::class,
        'fcm_token'        => \App\Models\FcmToken::class,
        'leave_request'    => \App\Models\LeaveRequest::class,
        'notification'     => \App\Models\Notification::class,
        'payment'          => \App\Models\Payment::class,
        'permission'       => \App\Models\Permission::class,
        'pricing'          => \App\Models\Pricing::class,
        'promo_code'       => \App\Models\PromoCode::class,
        'role'             => \App\Models\Role::class,
        'testimonial'      => \App\Models\Testimonial::class,
        'tourist_circuit'  => \App\Models\TouristCircuit::class,
        'user'             => \App\Models\User::class,
        'vehicle'          => \App\Models\Vehicle::class,
        'vehicle_contract' => \App\Models\VehicleContract::class,
        'vehicle_pause'    => \App\Models\VehiclePause::class,
        'zone'             => \App\Models\Zone::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // enforce* : lever une exception si un modèle polymorphe n'est pas déclaré,
        // plutôt que de réintroduire silencieusement un FQCN en base.
        Relation::enforceMorphMap(self::MORPH_MAP);

        /* if (env(key: 'APP_ENV') !== 'local') {
            URL::forceScheme(scheme: 'https');
        }
    }
}
