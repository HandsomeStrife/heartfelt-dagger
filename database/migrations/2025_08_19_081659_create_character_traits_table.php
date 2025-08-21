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
        Schema::create('character_traits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('character_id');
            $table->string('trait_name', 20)->index();
            $table->integer('trait_value');
            $table->timestamps();

            // Ensure unique trait per character
            $table->unique(['character_id', 'trait_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_traits');
    }
};
