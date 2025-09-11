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
        Schema::create('campaign_handouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('title', 200);
            $table->text('description')->nullable();
            $table->string('file_name', 255);
            $table->string('original_file_name', 255);
            $table->string('file_path', 500);
            $table->string('file_type', 50); // image, pdf, document, etc.
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size'); // in bytes
            $table->json('metadata')->nullable(); // for image dimensions, etc.
            
            $table->string('access_level', 20)->default('gm_only'); // gm_only, all_players, specific_players
            $table->boolean('is_visible_in_sidebar')->default(false); // show in room sidebar
            $table->integer('display_order')->default(0);
            $table->boolean('is_published')->default(true);

            $table->timestamps();

            // Indexes for performance
            $table->index(['campaign_id', 'access_level']);
            $table->index(['campaign_id', 'is_visible_in_sidebar']);
            $table->index(['campaign_id', 'display_order']);
            $table->index('creator_id');
            $table->index('file_type');

            // Full-text search index
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_handouts');
    }
};
