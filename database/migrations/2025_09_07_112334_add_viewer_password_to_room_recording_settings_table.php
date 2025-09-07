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
        Schema::table('room_recording_settings', function (Blueprint $table) {
            $table->string('viewer_password')->nullable()->after('stt_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recording_settings', function (Blueprint $table) {
            $table->dropColumn('viewer_password');
        });
    }
};
