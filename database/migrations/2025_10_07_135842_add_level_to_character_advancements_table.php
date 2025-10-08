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
        Schema::table('character_advancements', function (Blueprint $table) {
            // Drop the old unique constraint (character_id, tier, advancement_number)
            $table->dropUnique('char_adv_unique');
            
            // Add level column (tracks which character level this advancement was taken at)
            $table->integer('level')->after('tier')->default(1);
            
            // Add new unique constraint based on level instead of tier
            // This allows multiple level-ups per tier (e.g., level 2 and 3 are both tier 2)
            $table->unique(['character_id', 'level', 'advancement_number'], 'char_adv_level_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('character_advancements', function (Blueprint $table) {
            // Drop the new unique constraint
            $table->dropUnique('char_adv_level_unique');
            
            // Remove level column
            $table->dropColumn('level');
            
            // Restore the old unique constraint
            $table->unique(['character_id', 'tier', 'advancement_number'], 'char_adv_unique');
        });
    }
};
