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
        Schema::create('room_recordings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('provider', 20); // 'wasabi' or 'google_drive'
            $table->string('provider_file_id', 255); // file key/path for wasabi, file ID for google drive
            $table->string('filename', 255);
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->unsignedBigInteger('started_at_ms'); // recording start timestamp
            $table->unsignedBigInteger('ended_at_ms'); // recording end timestamp
            $table->string('mime_type', 100)->default('video/webm');
            $table->string('status', 20)->default('uploaded'); // uploaded, processing, ready, failed
            $table->timestamps();

            $table->index(['room_id']);
            $table->index(['user_id']);
            $table->index(['provider']);
            $table->index(['status']);
            $table->index(['started_at_ms']);
            $table->index(['ended_at_ms']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_recordings');
    }
};
