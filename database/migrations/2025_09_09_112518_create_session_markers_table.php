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
        Schema::create('session_markers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid'); // Shared UUID to identify all of the same markers
            $table->string('identifier', 255)->nullable(); // User-specified identifier (can be empty)
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade'); // User who created the marker
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // User this record is for
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade'); // Room the marker was created in
            $table->foreignId('recording_id')->nullable()->constrained('room_recordings')->onDelete('set null'); // Associated recording if any
            $table->integer('video_time')->nullable(); // Video recording time in seconds
            $table->integer('stt_time')->nullable(); // STT recording time in seconds
            $table->timestamps(); // created_at will be UTC time
            
            // Indexes for performance
            $table->index(['room_id']);
            $table->index(['creator_id']);
            $table->index(['user_id']);
            $table->index(['uuid']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_markers');
    }
};
