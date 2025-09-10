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
        // Check if there are existing characters
        $hasExistingCharacters = \Domain\Character\Models\Character::exists();

        if ($hasExistingCharacters) {
            // For existing installations with data
            Schema::table('characters', function (Blueprint $table) {
                // Add pronouns column
                $table->string('pronouns', 50)->nullable()->after('profile_image_path');

                // Add public_key column for sharing (without unique constraint initially)
                $table->string('public_key', 10)->nullable()->after('character_key');

                // Update character_key length from 8 to 10 characters
                $table->string('character_key', 10)->change();
            });

            // Generate public keys for existing characters
            $characters = \Domain\Character\Models\Character::all();
            foreach ($characters as $character) {
                $character->public_key = \Domain\Character\Models\Character::generateUniquePublicKey();
                $character->save();
            }

            // Now add the unique constraint and index
            Schema::table('characters', function (Blueprint $table) {
                $table->unique('public_key');
                $table->index('public_key');
            });
        } else {
            // For fresh installations
            Schema::table('characters', function (Blueprint $table) {
                // Add pronouns column
                $table->string('pronouns', 50)->nullable()->after('profile_image_path');

                // Add public_key column for sharing with unique constraint directly
                $table->string('public_key', 10)->unique()->after('character_key');

                // Update character_key length from 8 to 10 characters
                $table->string('character_key', 10)->change();

                // Add index for public_key for performance
                $table->index('public_key');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('characters', function (Blueprint $table) {
            // Remove added columns
            $table->dropColumn('pronouns');
            $table->dropIndex(['public_key']);
            $table->dropColumn('public_key');

            // Revert character_key length back to 8 characters
            $table->string('character_key', 8)->change();
        });
    }
};
