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
        Schema::table('room_transcripts', function (Blueprint $table) {
            // Add provider column to track STT source
            $table->string('provider', 20)->default('browser')->after('language');
            
            // Add character information to track which character is speaking
            $table->foreignId('character_id')->nullable()->constrained('characters')->onDelete('set null')->after('user_id');
            $table->string('character_name', 100)->nullable()->after('character_id');
            $table->string('character_class', 50)->nullable()->after('character_name');
            
            // Add indexes for performance
            $table->index(['provider']);
            $table->index(['character_id']);
            $table->index(['character_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_transcripts', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['provider']);
            $table->dropIndex(['character_id']);
            $table->dropIndex(['character_name']);
            
            // Drop foreign key constraint
            $table->dropForeign(['character_id']);
            
            // Drop columns
            $table->dropColumn(['provider', 'character_id', 'character_name', 'character_class']);
        });
    }
};