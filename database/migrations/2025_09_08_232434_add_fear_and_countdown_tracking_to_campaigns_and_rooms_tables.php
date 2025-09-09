<?php

declare(strict_types=1);

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
            $table->unsignedTinyInteger('fear_level')->default(0)->after('status');
            $table->json('countdown_trackers')->nullable()->after('fear_level');
            
            $table->index(['fear_level']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->unsignedTinyInteger('fear_level')->default(0)->after('status');
            $table->json('countdown_trackers')->nullable()->after('fear_level');
            
            $table->index(['fear_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaigns', function (Blueprint $table) {
            $table->dropIndex(['fear_level']);
            $table->dropColumn(['fear_level', 'countdown_trackers']);
        });

        Schema::table('rooms', function (Blueprint $table) {
            $table->dropIndex(['fear_level']);
            $table->dropColumn(['fear_level', 'countdown_trackers']);
        });
    }
};