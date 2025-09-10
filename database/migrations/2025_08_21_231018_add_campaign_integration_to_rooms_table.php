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
        Schema::table('rooms', function (Blueprint $table) {
            $table->foreignId('campaign_id')->nullable()->after('creator_id')->constrained('campaigns')->onDelete('cascade');
            $table->string('viewer_code', 12)->unique()->after('invite_code');

            $table->index(['campaign_id']);
            $table->index(['viewer_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rooms', function (Blueprint $table) {
            $table->dropForeign(['campaign_id']);
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['viewer_code']);
            $table->dropColumn(['campaign_id', 'viewer_code']);
        });
    }
};
