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
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description');
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('invite_code', 8)->unique();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            
            $table->index(['creator_id']);
            $table->index(['invite_code']);
            $table->index(['status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaigns');
    }
};
