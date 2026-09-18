<?php

namespace Tests\Feature\Fleet;

use App\Models\DriverContract;
use App\Models\Payment;
use App\Models\Vehicle;
use App\Models\VehicleContract;
use App\Models\VehiclePause;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Les fabriques ne servent à rien si l'objet qu'elles construisent ne ressemble pas à
 * la production. Ce test vérifie qu'un jeu complet s'insère, et que les accesseurs
 * calculés dont l'API dépend répondent — autrement dit que les clés étrangères sont
 * cohérentes ENTRE ELLES, et pas seulement valides une à une.
 */
class FactoriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_jeu_complet_s_insere_et_les_accesseurs_repondent(): void
    {
        $vehicle = Vehicle::factory()->create();
        $contract = VehicleContract::factory()->forVehicle($vehicle)->create();

        DriverContract::factory()->forVehicleContract($contract)->create();
        VehiclePause::factory()->forContract($contract)->create();
        Payment::factory()->create(['vehicle_contract_id' => $contract->id]);

        $contract->refresh();

        $this->assertSame($vehicle->owner_id, $contract->owner_id);
        $this->assertSame(24, $contract->contract_months);
        $this->assertGreaterThan(0, $contract->total_paid);
        $this->assertGreaterThan(0, $contract->daily_net_amount);
        $this->assertNotNull($contract->planned_end_date);
        $this->assertNotNull($contract->extended_end_date);
    }

    public function test_le_contrat_actif_est_celui_que_la_relation_renvoie(): void
    {
        $vehicle = Vehicle::factory()->create();
        VehicleContract::factory()->forVehicle($vehicle)->completed()->create();
        $active = VehicleContract::factory()->forVehicle($vehicle)->create();

        $this->assertSame($active->id, $vehicle->fresh()->activeVehicleContract->id);
    }

    public function test_une_pause_en_cours_est_la_pause_active_du_vehicule(): void
    {
        $vehicle = Vehicle::factory()->create();
        $contract = VehicleContract::factory()->forVehicle($vehicle)->create();
        VehiclePause::factory()->forContract($contract)->create();
        $ongoing = VehiclePause::factory()->forContract($contract)->ongoing()->create();

        $this->assertSame($ongoing->id, $vehicle->fresh()->activePause->id);
    }
}
