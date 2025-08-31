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
        Schema::create('room_transcripts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedBigInteger('started_at_ms'); // transcript chunk start timestamp
            $table->unsignedBigInteger('ended_at_ms'); // transcript chunk end timestamp
            $table->text('text'); // transcribed text content
            $table->string('language', 10)->default('en-US'); // speech recognition language
            $table->float('confidence', 3, 2)->nullable(); // confidence score if available
            $table->timestamps();

            $table->index(['room_id']);
            $table->index(['user_id']);
            $table->index(['started_at_ms']);
            $table->index(['ended_at_ms']);
            $table->index(['language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_transcripts');
    }
};
