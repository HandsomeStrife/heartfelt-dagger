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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('description', 500);
            $table->string('password');
            $table->tinyInteger('guest_count')->unsigned();
            $table->foreignId('creator_id')->constrained('users')->onDelete('cascade');
            $table->string('invite_code', 8)->unique();
            $table->string('status', 20)->default('active');
            $table->timestamps();

            $table->index(['creator_id']);
            $table->index(['invite_code']);
            $table->index(['status']);
            $table->index(['guest_count']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
