<div x-data="characterViewerState({
    canEdit: @js($can_edit),
    isAuthenticated: @js(auth()->check()),
    characterKey: @js($character_key),
    final_hit_points: @js($computed_stats['final_hit_points'] ?? 6),
    stress_len: @js($computed_stats['stress'] ?? 6),
    armor_score: @js($computed_stats['armor_score'] ?? 0)
})" class="bg-slate-950 text-slate-100/95 antialiased min-h-screen"
    style="font-family: Inter, ui-sans-serif, system-ui, -apple-system, Segoe UI, Roboto, 'Helvetica Neue', Arial, 'Apple Color Emoji', 'Segoe UI Emoji';">

    <main class="max-w-7xl mx-auto p-6 md:p-8 space-y-6">

        <!-- TOP BANNER -->
        <x-character-viewer.top-banner
            :character="$character"
            :pronouns="$pronouns"
            :class-data="$class_data"
            :subclass-data="$subclass_data"
            :ancestry-data="$ancestry_data"
            :community-data="$community_data"
            :computed-stats="$computed_stats"
            :can-edit="$can_edit"
            :trait-info="$this->getTraitInfo()"
        />

        <!-- MAIN: Left = Damage & Health, Right = Hope + Gold -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <!-- Left: DAMAGE & HEALTH -->
            <div class="lg:col-span-7">
                <x-character-viewer.damage-health :computed-stats="$computed_stats" />

                <x-character-viewer.active-weapons :organized-equipment="$organized_equipment" :character="$character" />

                <x-character-viewer.active-armor :organized-equipment="$organized_equipment" />
            </div>

            <!-- Right: HOPE + GOLD -->
            <div class="lg:col-span-5 space-y-6">
                <x-character-viewer.hope :class-data="$class_data" />

                <x-character-viewer.gold />

                <x-character-viewer.experience :character="$character" />
            </div>
        </section>

        <!-- FEATURES -->
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            <div class="lg:col-span-12 grid grid-cols-1 gap-6">
                <!-- Domain Effects as Cards -->
                <x-character-viewer.domain-cards :domain-card-details="$domain_card_details" />
            </div>
        </section>

        <!-- JOURNAL -->
        <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <x-character-viewer.equipment :organized-equipment="$organized_equipment" />
            <x-character-viewer.journal :character="$character" />
        </section>
    </main>
</div>
