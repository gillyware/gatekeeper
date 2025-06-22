<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('gatekeeper.tables.model_has_permissions', 'model_has_permissions'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('permission_id')
                ->constrained(config('gatekeeper.tables.permissions', 'permissions'))
                ->cascadeOnDelete();

            $table->morphs('model');

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['permission_id', 'model_type', 'model_id'], 'model_permission_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('gatekeeper.tables.model_has_permissions', 'model_has_permissions'));
    }
};
