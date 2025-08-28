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
        Schema::create('campaign_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('campaign_pages')->cascadeOnDelete();
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();
            
            $table->string('title', 200);
            $table->longText('content')->nullable();
            $table->json('category_tags')->nullable();
            $table->string('access_level', 20)->default('gm_only'); // gm_only, all_players, specific_players
            $table->integer('display_order')->default(0);
            $table->boolean('is_published')->default(true);
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['campaign_id', 'parent_id']);
            $table->index(['campaign_id', 'display_order']);
            $table->index(['campaign_id', 'access_level']);
            $table->index('creator_id');
            
            // Full-text search index
            $table->fullText(['title', 'content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_pages');
    }
};
