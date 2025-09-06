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
        Schema::table('room_participants', function (Blueprint $table) {
            $table->boolean('recording_consent_given')->nullable()->after('stt_consent_at');
            $table->timestamp('recording_consent_at')->nullable()->after('recording_consent_given');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_participants', function (Blueprint $table) {
            $table->dropColumn(['recording_consent_given', 'recording_consent_at']);
        });
    }
};