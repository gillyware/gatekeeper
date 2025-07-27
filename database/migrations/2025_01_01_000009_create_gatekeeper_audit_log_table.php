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
        $tableName = Config::get('gatekeeper.tables.audit_log', GatekeeperConfigDefault::TABLES_AUDIT_LOG);

        Schema::create($tableName, function (Blueprint $table) {
            $table->id();

            $table->string('action')->index();

            $table->nullableMorphs('action_by_model');
            $table->nullableMorphs('action_to_model');

            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableName = Config::get('gatekeeper.tables.audit_log', GatekeeperConfigDefault::TABLES_AUDIT_LOG);

        Schema::dropIfExists($tableName);
    }
};
