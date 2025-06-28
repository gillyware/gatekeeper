<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(Config::get('gatekeeper.tables.model_has_teams'), function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained(Config::get('gatekeeper.tables.teams'))
                ->cascadeOnDelete();

            $table->morphs('model');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Config::get('gatekeeper.tables.model_has_teams'));
    }
};
