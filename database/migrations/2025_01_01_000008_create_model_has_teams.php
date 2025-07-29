<?php

use Gillyware\Gatekeeper\Constants\GatekeeperConfigDefault;
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
        Schema::create(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS), function (Blueprint $table) {
            $table->id();

            $table->foreignId('team_id')
                ->constrained(Config::get('gatekeeper.tables.teams', GatekeeperConfigDefault::TABLES_TEAMS))
                ->cascadeOnDelete();

            $table->morphs('model');
            $table->boolean('denied')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Config::get('gatekeeper.tables.model_has_teams', GatekeeperConfigDefault::TABLES_MODEL_HAS_TEAMS));
    }
};
