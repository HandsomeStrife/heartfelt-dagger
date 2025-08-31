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
        Schema::create('campaign_page_access', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_page_id')->constrained('campaign_pages')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // Prevent duplicate access records
            $table->unique(['campaign_page_id', 'user_id']);
            
            // Indexes for performance
            $table->index('campaign_page_id');
            $table->index('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_page_access');
    }
};
