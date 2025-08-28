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
        Schema::create('campaign_frame_visibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('campaigns')->cascadeOnDelete();
            $table->string('section_name', 50);
            $table->boolean('is_visible_to_players')->default(false);
            $table->timestamps();

            // Ensure one setting per campaign per section
            $table->unique(['campaign_id', 'section_name']);
            
            // Indexes for performance
            $table->index(['campaign_id']);
            $table->index(['campaign_id', 'is_visible_to_players'], 'cfv_campaign_visibility_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_frame_visibilities');
    }
};
