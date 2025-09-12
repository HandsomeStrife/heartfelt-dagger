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
        Schema::table('room_participants', function (Blueprint $table) {
            $table->boolean('local_save_consent_given')->nullable()->after('recording_consent_at');
            $table->timestamp('local_save_consent_given_at')->nullable()->after('local_save_consent_given');
            $table->boolean('local_save_consent_denied')->nullable()->after('local_save_consent_given_at');
            $table->timestamp('local_save_consent_denied_at')->nullable()->after('local_save_consent_denied');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_participants', function (Blueprint $table) {
            $table->dropColumn([
                'local_save_consent_given',
                'local_save_consent_given_at',
                'local_save_consent_denied',
                'local_save_consent_denied_at',
            ]);
        });
    }
};
