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
        Schema::create('character_equipment', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('character_id');
            $table->string('equipment_type', 20)->index();
            $table->string('equipment_key', 100);
            $table->json('equipment_data')->nullable();
            $table->boolean('is_equipped')->default(true);
            $table->timestamps();

            // Index for performance
            $table->index(['character_id', 'equipment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_equipment');
    }
};
