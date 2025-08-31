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
        Schema::create('characters', function (Blueprint $table) {
            $table->id();
            $table->string('character_key', 8)->unique();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name', 100)->nullable();
            $table->string('class', 50)->nullable();
            $table->string('subclass', 50)->nullable();
            $table->string('ancestry', 50)->nullable();
            $table->string('community', 50)->nullable();
            $table->integer('level')->default(1);
            $table->string('profile_image_path')->nullable();
            $table->json('character_data');
            $table->boolean('is_public')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index('character_key');
            $table->index('user_id');
            $table->index('class');
            $table->index('ancestry');
            $table->index('community');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('characters');
    }
};
