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
            $table->boolean('stt_consent_given')->nullable()->after('left_at');
            $table->timestamp('stt_consent_at')->nullable()->after('stt_consent_given');
            
            $table->index(['stt_consent_given']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_participants', function (Blueprint $table) {
            $table->dropIndex(['stt_consent_given']);
            $table->dropColumn(['stt_consent_given', 'stt_consent_at']);
        });
    }
};
