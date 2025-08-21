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
        Schema::create('character_domain_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('character_id');
            $table->string('domain', 50);
            $table->string('ability_key', 100);
            $table->integer('ability_level');
            $table->timestamps();

            // Index for performance
            $table->index(['character_id', 'domain']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_domain_cards');
    }
};
