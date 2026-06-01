<?php

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
        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE uuid USING model_id::uuid');
        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE uuid USING model_id::uuid');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE model_has_roles ALTER COLUMN model_id TYPE varchar USING model_id::text');
        DB::statement('ALTER TABLE model_has_permissions ALTER COLUMN model_id TYPE varchar USING model_id::text');
    }
};
