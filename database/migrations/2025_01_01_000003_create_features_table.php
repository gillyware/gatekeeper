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
        Schema::create(Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES), function (Blueprint $table) {
            $table->id();

            $table->string('name')->index();
            $table->boolean('is_active')->default(true);
            $table->boolean('grant_by_default')->default(false);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(Config::get('gatekeeper.tables.features', GatekeeperConfigDefault::TABLES_FEATURES));
    }
};
