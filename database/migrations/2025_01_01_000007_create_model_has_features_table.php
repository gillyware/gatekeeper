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
        Schema::create(Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES), function (Blueprint $table) {
            $table->id();

            $table->foreignId('feature_id')
                ->constrained(Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES))
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
        Schema::dropIfExists(Config::get('gatekeeper.tables.model_has_features', GatekeeperConfigDefault::TABLES_MODEL_HAS_FEATURES));
    }
};
