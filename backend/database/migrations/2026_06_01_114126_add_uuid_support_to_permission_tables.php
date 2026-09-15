<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $tableNames = config('permission.table_names');

        // Drop existing foreign keys first
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Modify model_id to string to support UUIDs
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->string('model_id')->change();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->string('model_id')->change();
        });

        // Recreate primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');

        // Drop primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Revert to bigint
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->unsignedBigInteger('model_id')->change();
        });

        // Recreate primary keys
        Schema::table($tableNames['model_has_permissions'], function (Blueprint $table) {
            $table->primary(['permission_id', 'model_id', 'model_type']);
        });

        Schema::table($tableNames['model_has_roles'], function (Blueprint $table) {
            $table->primary(['role_id', 'model_id', 'model_type']);
        });
    }
};
