<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, update the default value for future characters
        Schema::table('characters', function (Blueprint $table) {
            $table->integer('proficiency')->default(1)->change();
        });

        // Then, fix existing characters that have proficiency 0
        // Calculate correct proficiency based on level (DaggerHeart SRD rules)
        DB::statement('
            UPDATE characters 
            SET proficiency = CASE 
                WHEN level <= 1 THEN 1
                WHEN level <= 4 THEN 2
                WHEN level <= 7 THEN 3
                ELSE 4
            END
            WHERE proficiency = 0
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            $table->integer('proficiency')->default(0)->change();
        });
    }
};