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
        Schema::table('character_traits', function (Blueprint $table) {
            $table->boolean('is_marked')->default(false)->after('trait_value');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_traits', function (Blueprint $table) {
            $table->dropColumn('is_marked');
        });
    }
};
