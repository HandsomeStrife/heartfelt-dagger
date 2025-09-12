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
        Schema::create('room_recording_errors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('recording_id')->nullable()->constrained('room_recordings')->onDelete('cascade');
            $table->string('error_type'); // 'finalization_failed', 'upload_failed', 'validation_failed', 'consent_error', etc.
            $table->string('error_code')->nullable(); // HTTP status codes, provider error codes, etc.
            $table->text('error_message');
            $table->json('error_context')->nullable(); // Additional context like request data, stack traces, etc.
            $table->string('provider')->nullable(); // 'wasabi', 'google_drive', 'local', etc.
            $table->string('multipart_upload_id')->nullable(); // For tracking specific uploads
            $table->string('provider_file_id')->nullable(); // File ID from storage provider
            $table->timestamp('occurred_at');
            $table->boolean('resolved')->default(false);
            $table->text('resolution_notes')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            
            $table->index(['room_id', 'occurred_at']);
            $table->index(['user_id', 'occurred_at']);
            $table->index(['error_type', 'occurred_at']);
            $table->index(['provider', 'occurred_at']);
            $table->index(['resolved', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('room_recording_errors');
    }
};
