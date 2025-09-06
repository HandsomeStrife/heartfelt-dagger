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
        Schema::table('room_recording_settings', function (Blueprint $table) {
            // Add consent requirement settings
            $table->enum('stt_consent_requirement', ['optional', 'required'])->default('optional')->after('stt_enabled');
            $table->enum('recording_consent_requirement', ['optional', 'required'])->default('optional')->after('storage_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recording_settings', function (Blueprint $table) {
            $table->dropColumn(['stt_consent_requirement', 'recording_consent_requirement']);
        });
    }
};
