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
        Schema::table('room_recordings', function (Blueprint $table) {
            $table->text('stream_url')->nullable()->after('status');
            $table->string('thumbnail_url')->nullable()->after('stream_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recordings', function (Blueprint $table) {
            $table->dropColumn(['stream_url', 'thumbnail_url']);
        });
    }
};