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
        Schema::create('campaign_handout_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_handout_id')->constrained('campaign_handouts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            
            $table->timestamps();

            // Ensure unique access per handout per user
            $table->unique(['campaign_handout_id', 'user_id']);
            
            // Indexes for performance
            $table->index('campaign_handout_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_handout_access');
    }
};
