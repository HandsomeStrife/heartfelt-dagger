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
        Schema::create('user_storage_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('provider', 20); // 'wasabi' or 'google_drive'
            $table->text('encrypted_credentials'); // JSON with provider-specific data
            $table->string('display_name', 100); // User-friendly name
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id']);
            $table->index(['provider']);
            $table->index(['is_active']);
            $table->unique(['user_id', 'provider', 'display_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_storage_accounts');
    }
};
