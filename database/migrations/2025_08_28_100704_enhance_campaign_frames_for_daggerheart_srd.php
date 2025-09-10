<?php

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
        Schema::table('campaign_frames', function (Blueprint $table) {
            // Add new fields according to DaggerHeart SRD structure
            $table->json('touchstones')->nullable()->after('pitch');
            $table->json('tone')->nullable()->after('touchstones');
            $table->json('themes')->nullable()->after('tone');
            $table->json('player_principles')->nullable()->after('themes');
            $table->json('gm_principles')->nullable()->after('player_principles');
            $table->json('community_guidance')->nullable()->after('gm_principles');
            $table->json('ancestry_guidance')->nullable()->after('community_guidance');
            $table->json('class_guidance')->nullable()->after('ancestry_guidance');
            $table->json('campaign_mechanics')->nullable()->after('class_guidance');
        });

        // Data migration: move combined fields to separate fields
        $frames = DB::table('campaign_frames')->get();

        foreach ($frames as $frame) {
            $toneAndThemes = json_decode($frame->tone_and_themes, true) ?? [];
            $principles = json_decode($frame->principles, true) ?? [];

            // Try to intelligently split tone_and_themes into tone and themes
            // Tone words are typically adjectives (Adventurous, Epic, etc.)
            // Theme words are typically concepts (Cultural Clash, etc.)
            $tone = [];
            $themes = [];

            foreach ($toneAndThemes as $item) {
                // Basic heuristic: if it contains spaces or specific theme keywords, it's likely a theme
                if (str_contains($item, ' ') ||
                    str_contains(strtolower($item), 'vs') ||
                    str_contains(strtolower($item), 'clash') ||
                    str_contains(strtolower($item), 'transformation') ||
                    str_contains(strtolower($item), 'survival') ||
                    str_contains(strtolower($item), 'grief')) {
                    $themes[] = $item;
                } else {
                    $tone[] = $item;
                }
            }

            // Update the record with new structure
            DB::table('campaign_frames')
                ->where('id', $frame->id)
                ->update([
                    'touchstones' => json_encode([]),
                    'tone' => json_encode($tone),
                    'themes' => json_encode($themes),
                    'player_principles' => json_encode($principles), // Initially move all principles to player
                    'gm_principles' => json_encode([]),
                    'community_guidance' => json_encode([]),
                    'ancestry_guidance' => json_encode([]),
                    'class_guidance' => json_encode([]),
                    'campaign_mechanics' => $frame->special_mechanics, // Copy special_mechanics to campaign_mechanics
                ]);
        }

        // Remove old combined fields
        Schema::table('campaign_frames', function (Blueprint $table) {
            $table->dropColumn(['tone_and_themes', 'principles']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('campaign_frames', function (Blueprint $table) {
            // Re-add the old fields
            $table->json('tone_and_themes')->nullable()->after('pitch');
            $table->json('principles')->nullable()->after('setting_guidance');
        });

        // Data migration: combine new fields back into old structure
        $frames = DB::table('campaign_frames')->get();

        foreach ($frames as $frame) {
            $tone = json_decode($frame->tone, true) ?? [];
            $themes = json_decode($frame->themes, true) ?? [];
            $playerPrinciples = json_decode($frame->player_principles, true) ?? [];
            $gmPrinciples = json_decode($frame->gm_principles, true) ?? [];

            $combinedToneAndThemes = array_merge($tone, $themes);
            $combinedPrinciples = array_merge($playerPrinciples, $gmPrinciples);

            DB::table('campaign_frames')
                ->where('id', $frame->id)
                ->update([
                    'tone_and_themes' => json_encode($combinedToneAndThemes),
                    'principles' => json_encode($combinedPrinciples),
                ]);
        }

        // Drop new fields
        Schema::table('campaign_frames', function (Blueprint $table) {
            $table->dropColumn([
                'touchstones',
                'tone',
                'themes',
                'player_principles',
                'gm_principles',
                'community_guidance',
                'ancestry_guidance',
                'class_guidance',
                'campaign_mechanics',
            ]);
        });
    }
};
