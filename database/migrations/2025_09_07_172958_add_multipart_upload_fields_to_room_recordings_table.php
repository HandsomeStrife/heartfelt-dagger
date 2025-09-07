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
            $table->string('multipart_upload_id', 255)->nullable()->after('provider_file_id');
            $table->json('uploaded_parts')->nullable()->after('multipart_upload_id'); // Store part numbers and ETags
            $table->string('status', 20)->default('recording')->change(); // recording, finalizing, completed, failed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recordings', function (Blueprint $table) {
            $table->dropColumn(['multipart_upload_id', 'uploaded_parts']);
            $table->string('status', 20)->default('uploaded')->change();
        });
    }
};
