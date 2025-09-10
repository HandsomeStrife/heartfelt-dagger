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
            $table->string('stt_provider', 20)->nullable()->after('stt_enabled'); // 'browser' or 'assemblyai'
            $table->foreignId('stt_account_id')->nullable()->after('stt_provider')->constrained('user_storage_accounts')->onDelete('set null');

            $table->index(['stt_provider']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recording_settings', function (Blueprint $table) {
            $table->dropForeign(['stt_account_id']);
            $table->dropIndex(['stt_provider']);
            $table->dropColumn(['stt_provider', 'stt_account_id']);
        });
    }
};
