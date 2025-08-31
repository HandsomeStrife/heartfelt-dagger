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
        Schema::create('character_advancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('character_id')->constrained('characters')->onDelete('cascade');
            $table->integer('tier'); // 1-4
            $table->integer('advancement_number'); // 1-2 (each tier has 2 advancement choices)
            $table->string('advancement_type'); // trait_bonus, hit_point, stress, experience_bonus, domain_card, evasion, subclass, proficiency, multiclass
            $table->json('advancement_data'); // Stores specific details like which traits, multiclass selection, etc.
            $table->string('description'); // Human readable description of what was selected
            $table->timestamps();

            // Indexes for performance
            $table->index(['character_id', 'tier']);
            $table->unique(['character_id', 'tier', 'advancement_number'], 'char_adv_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('character_advancements');
    }
};