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
        Schema::create('campaign_frames', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->tinyInteger('complexity_rating');
            $table->boolean('is_public')->default(false);
            $table->foreignId('creator_id')->constrained('users')->cascadeOnDelete();

            // Campaign frame content fields stored as JSON
            $table->json('pitch')->nullable();
            $table->json('tone_and_themes')->nullable();
            $table->text('background_overview')->nullable();
            $table->json('setting_guidance')->nullable();
            $table->json('principles')->nullable();
            $table->json('setting_distinctions')->nullable();
            $table->text('inciting_incident')->nullable();
            $table->json('special_mechanics')->nullable();
            $table->json('session_zero_questions')->nullable();

            $table->timestamps();

            // Indexes for performance
            $table->index(['is_public', 'name']);
            $table->index('creator_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_frames');
    }
};
