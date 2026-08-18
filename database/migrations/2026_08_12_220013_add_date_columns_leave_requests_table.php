<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->uuid('vehicle_pause_id')->nullable()->after('driver_contract_id');
            $table->date('start_date')->nullable()->after('vehicle_pause_id');
            $table->unsignedInteger('requested_days')->nullable()->after('start_date');
            $table->date('end_date')->nullable()->after('requested_days');
            $table->unsignedInteger('effective_days')->nullable()->after('end_date');
            $table->string('source')->default('driver_request')->after('effective_days'); // driver_request | admin_instant | admin_historical | legacy
            $table->uuid('created_by')->nullable()->after('source');
        });

        // ── Remplacer le check constraint sur `status` pour accepter les nouvelles valeurs ──
        DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_status_check');
        DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'ongoing', 'completed'))");

        // ── Migration des données existantes (colonne `dates` JSON) ──
        DB::table('leave_requests')->orderBy('id')->chunkById(100, function ($rows) {
            foreach ($rows as $row) {
                $dates = json_decode($row->dates ?? '[]', true) ?: [];
                if (empty($dates)) {
                    continue;
                }
                sort($dates);
                $start = $dates[0];
                $end   = end($dates);
                $count = count($dates);

                $update = [
                    'start_date'     => $start,
                    'requested_days' => $count,
                    'source'         => 'legacy',
                ];

                if ($row->status === 'approved') {
                    if (Carbon::parse($end)->lt(Carbon::today())) {
                        $update['status']         = 'completed';
                        $update['end_date']       = $end;
                        $update['effective_days'] = $count;
                    } else {
                        $update['status'] = 'ongoing';
                    }
                }

                DB::table('leave_requests')->where('id', $row->id)->update($update);
            }
        });

        // ── Resserrer le constraint : `approved` n'existe plus une fois la migration terminée ──
        DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_status_check');
        DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_status_check CHECK (status IN ('pending', 'rejected', 'ongoing', 'completed'))");

        Schema::table('leave_requests', function (Blueprint $table) {
            $table->dropColumn('dates');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('leave_requests', function (Blueprint $table) {
            $table->json('dates')->nullable();
            $table->dropColumn([
                'vehicle_pause_id',
                'start_date',
                'requested_days',
                'end_date',
                'effective_days',
                'source',
                'created_by'
            ]);
        });

        DB::statement('ALTER TABLE leave_requests DROP CONSTRAINT IF EXISTS leave_requests_status_check');
        DB::statement("ALTER TABLE leave_requests ADD CONSTRAINT leave_requests_status_check CHECK (status IN ('pending', 'approved', 'rejected'))");
    }
};
