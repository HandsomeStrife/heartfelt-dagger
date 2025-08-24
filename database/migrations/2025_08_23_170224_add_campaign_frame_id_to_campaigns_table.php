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
        Schema::table('campaigns', function (Blueprint $table) {
            $table->foreignId('campaign_frame_id')->nullable()->constrained('campaign_frames')->nullOnDelete();
            $table->index('campaign_frame_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropForeign(['campaign_frame_id']);
            $table->dropIndex(['campaign_frame_id']);
            $table->dropColumn('campaign_frame_id');
        });
    }
};
