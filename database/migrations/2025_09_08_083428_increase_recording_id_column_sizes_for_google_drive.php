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
            // Increase column sizes to accommodate Google Drive session URIs which can be very long
            $table->string('provider_file_id', 2000)->change();
            $table->string('multipart_upload_id', 2000)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recordings', function (Blueprint $table) {
            // Revert to original sizes
            $table->string('provider_file_id', 255)->change();
            $table->string('multipart_upload_id', 255)->nullable()->change();
        });
    }
};
