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
        Schema::create('character_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->onDelete('cascade');
            
            // Interactive state - stored as JSON arrays of boolean values
            $table->json('hit_points')->nullable(); // Array of marked HP slots
            $table->json('stress')->nullable(); // Array of marked stress slots
            $table->json('hope')->nullable(); // Array of marked hope slots
            $table->json('armor_slots')->nullable(); // Array of marked armor slots
            $table->json('gold_handfuls')->nullable(); // Array of marked gold handfuls
            $table->json('gold_bags')->nullable(); // Array of marked gold bags
            $table->boolean('gold_chest')->default(false); // Single gold chest state
            
            $table->timestamps();

            // Ensure one status record per character
            $table->unique('character_id');
            
            // Index for performance
            $table->index('character_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_status');
    }
};