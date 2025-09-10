<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First clear any existing thumbnail URLs that might be too long
        DB::table('room_recordings')->update(['thumbnail_url' => null]);

        Schema::table('room_recordings', function (Blueprint $table) {
            $table->text('thumbnail_url')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('room_recordings', function (Blueprint $table) {
            $table->string('thumbnail_url')->change();
        });
    }
};
