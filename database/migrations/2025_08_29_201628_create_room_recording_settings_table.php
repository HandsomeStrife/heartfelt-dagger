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
        Schema::create('room_recording_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->boolean('recording_enabled')->default(false);
            $table->boolean('stt_enabled')->default(false); // speech-to-text
            $table->string('storage_provider', 20)->nullable(); // 'wasabi' or 'google_drive'
            $table->foreignId('storage_account_id')->nullable()->constrained('user_storage_accounts')->onDelete('set null');
            $table->timestamps();

            $table->unique(['room_id']);
            $table->index(['storage_provider']);
            $table->index(['recording_enabled']);
            $table->index(['stt_enabled']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_recording_settings');
    }
};
