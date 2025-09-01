## DaggerHeart Character Builder & Viewer — Comprehensive Test Plan

### Part 1 — Altering Effects Inventory and Expected Outcomes

This section enumerates only altering effects — things that change stats, selection limits, or computed values at creation or persistently. Non-numeric narrative features are included only when they impose a builder constraint or visible viewer requirement.

- **Global Rules and Cross-Cutting Validations**
  - **Trait distribution**: Must be exactly -1, 0, 0, +1, +1, +2. Builder enforces; viewer displays final assigned values.
  - **Class base stats**: Each class defines `startingEvasion` and `startingHitPoints`. Builder computes final values using base + modifiers; viewer must match.
  - **Stress slots**: Base 6 for all PCs; can be modified by ancestry/subclass effects where specified.
  - **Damage thresholds**:
    - Computed from armor score and bonuses; Major = level (1) + 3 + armor + ancestry/subclass bonuses; Severe = level (1) + 8 + armor + ancestry/subclass bonuses.
    - Armor score is derived from selected armor’s `baseScore|armor_score|score` additive.
  - **Domains and domain cards**:
    - Class grants exactly 2 domains; starting characters select exactly 2 domain cards total, only from class domains and only level 1.
    - Subclass effects may increase allowed domain cards.
  - **Starting equipment gating**:
    - Must have a primary weapon and armor.
    - Must satisfy class `startingInventory` “chooseOne” and “chooseExtra” where present.
  - **Playtest labeling**:
    - Any ancestry/community flagged as playtest must display “Void - Playtest vX.X” label across builder and viewer.

- **Classes (Base Alterations)**
  - All classes
    - **Base Evasion / Hit Points**: Set by class data; builder and viewer must reflect these in computed stats.
    - **Domains (2 per class)**: Governs ability filters in builder and viewer.
    - **Starting inventory constraints**: Enforce “always”, “chooseOne”, “chooseExtra” in builder completion logic.
    - **Suggested equipment**: Builder provides suggested primary/secondary weapons and armor; used for quick-fill but not mandatory.
  - Expected outcomes:
    - Builder: Final Evasion and HP reflect base + modifiers; domain card filter shows only level 1 cards from the class domains; equipment step blocks completion until constraints satisfied.
    - Viewer: Displays the selected class name, domains, starting items (organized), and computed stats consistent with builder.

- **Class → Domain pairs (from classes.json; assert exactly these in UI)**
  - Bard: Grace + Codex
  - Druid: Sage + Arcana
  - Guardian: Valor + Blade
  - Ranger: Bone + Sage
  - Rogue: Midnight + Grace
  - Seraph: Splendor + Valor
  - Sorcerer: Arcana + Midnight
  - Warrior: Blade + Bone
  - Wizard: Codex + Splendor
  - Witch: Dread + Sage
  - Warlock: Dread + Grace
  - Brawler: Bone + Valor
  - Assassin: Midnight + Blade

- **Subclasses (encoded altering effects)**
  - Only subclass features with an `effects` array are applied to computed stats or card limits. Validate these:
    - School of Knowledge → “Accomplished”: `domain_card_bonus +1` (permanent)
      - Expected: Builder increases max domain cards from 2 to 3; viewer displays 3 selected cards.
    - School of War → “Battlemage”: `hit_point_bonus +1` (permanent)
      - Expected: +1 Hit Point slot reflected in builder computed stats and viewer.
    - Nightwalker → “Fleeting Shadow”: `evasion_bonus +1` (permanent)
      - Expected: +1 Evasion reflected.
    - Stalwart → “Unwavering”/“Unrelenting”/“Undaunted”: `damage_threshold_bonus +1/+2/+3` (permanent)
      - Expected: Cumulative increase to damage thresholds reflected.
    - Winged Sentinel → “Ascendant”: `severe_threshold_bonus +4` (permanent)
      - Expected: Severe threshold increased by 4.
    - Vengeance → “At Ease”: `stress_bonus +1` (permanent)
      - Expected: Stress slots increased from 6 to 7.
  - Subclasses without `effects`: verify no unintended numeric changes; still render feature text in viewer.

- **Ancestries (encoded altering effects)**
  - Clank → “Purposeful Design”: `experience_bonus_selection +1` at character creation (choose one experience)
    - Expected: Builder allows selecting one of the user’s experiences for +1 (stored in `clank_bonus_experience`); viewer shows that experience as +3 instead of +2.
  - Galapa → “Shell”: `damage_threshold_bonus = proficiency` (at creation; level 1 proficiency = 2)
    - Expected: +2 to both thresholds.
  - Giant → “Endurance”: `hit_point_bonus +1` (at creation)
    - Expected: +1 Hit Point slot.
  - Human → “High Stamina”: `stress_bonus +1` (at creation)
    - Expected: Stress slots increased from 6 to 7.
  - Simiah → “Nimble”: `evasion_bonus +1` (at creation)
    - Expected: +1 Evasion.
  - Earthkin (Playtest) → “Stoneskin”: `armor_score_bonus +1`, `damage_threshold_bonus +1` (permanent)
    - Expected: Armor score +1 and thresholds +1; playtest label visible.
  - Other ancestries: no numeric creation-time effects; ensure features display correctly in viewer.

- **Communities**
  - No numeric creation-time effects encoded. Viewer must display the community feature text. Playtest communities must display the playtest label.

- **Domains and Abilities (Level 1 selection constraints)**
  - Filtering: Builder filters domain cards to the two class domains, level 1 only.
  - Selection limits: Exactly 2 cards total unless subclass grants `domain_card_bonus`.
  - Viewer display: Shows selected cards with full ability data (name, type, recallCost, description), matching ability keys.
  - Include new content domains where present (e.g., Dread). Ensure level-1 abilities for those domains filter and display correctly for the classes that grant them.
  - Expected outcomes: Out-of-domain or >level 1 selection blocked; deselect toggles work.

### Abilities Coverage and Validation (All Levels)

Validate the entirety of `abilities.json` against `domains.json` (not just level 1), and ensure UI renders selected level-1 cards correctly while the data remains internally consistent for levels 1–10.

- Cross-reference integrity per domain (levels 1–10)
  - For each domain in `domains.json`, for every level block (1–10):
    - Every ability key listed in `abilitiesByLevel[level].abilities` exists in `abilities.json`.
    - The corresponding `abilities.json` entry has `domain` equal to that domain key and the same `level` value.
  - Fail if any listed ability is missing, has a mismatched domain, or mismatched level.

- Ability schema validation (every entry in `abilities.json`)
  - `domain` is one of: arcana, blade, bone, codex, dread, grace, midnight, sage, splendor, valor.
  - `level` is an integer in [1..10].
  - `type` is one of: Ability, Spell, Grimoire (expand allowed set if project supports more).
  - `recallCost` is a number (>= 0). Validate presence even when 0.
  - `descriptions` is a non-empty array of strings; no empty strings.
  - If `playtest.isPlaytest` is true, `playtest.label` is present and correctly rendered by UI.
  - Keys are unique; `name` is consistent with `key` (case/spacing differences allowed, but not contradictions).

- Level-specific coverage expectations
  - Validate presence of domain “touched” cards at level 7: `arcanatouched`, `bladetouched`, `bonetouched`, `codextouched`, `gracetouched`, `midnighttouched`, `sagetouched`, `splendortouched`, `valortouched`, and (playtest) `dreadtouched`.
  - Validate representative high-tier cards (levels 8–10) exist for each domain and that `recallCost` is set as defined.
  - Validate Codex Grimoire entries (e.g., `book of ...`) have `type: Grimoire` and expected recall costs.
  - Validate Dread (playtest) abilities carry playtest flags and are labeled in UI when displayed.

- UI rendering checks (applied to selected level-1 cards; data consistency checked across all levels)
  - Viewer for a selected level-1 card: show `name`, `type`, `recallCost`, and all `descriptions` lines.
  - If a card has `playtest`, viewer shows “Void - Playtest vX.X”.
  - Ensure long description arrays render line-breaks correctly and without truncation.

- Data completeness checks
  - No orphaned abilities: every `abilities.json` entry should appear in some `domains.json.abilitiesByLevel[*].abilities` list (unless intentionally excluded—flag if not found).
  - No duplicate listing across multiple levels for the same key; level progression is unique.

Notes
- Builder restricts selection to level 1 by design; tests still validate that higher-level abilities are present, correctly typed, and accurately mapped for future-proofing and data integrity.


### Part 2 — Browser Workflow Tests to Create

Write each as a browser test scenario validating UI, state, and persistence. Include both Builder and Viewer.

- **Builder Initialization and Navigation**
  - Load JSON data: `classes`, `subclasses`, `ancestries`, `communities`, `domains`, `abilities`, `weapons`, `armor`, `items`, `consumables` load without error.
  - Tabs present for all steps; free navigation allowed; completion indicators update live.

- **Class Selection & Domain Filtering**
  - For each class:
    - Computed stats show base `startingEvasion`/`startingHitPoints` prior to ancestry/subclass bonuses.
    - Domain cards list only level 1 cards from the class’s two domains.
    - Suggested equipment is populated; background and connection questions render.
  - Class-domain SRD compliance: assert domains match JSON/SRD for every class.

- **Subclass Effects Application**
  - No subclass: max domain cards = 2.
  - School of Knowledge: max domain cards = 3; UI allows 3; viewer shows 3.
  - School of War: +1 HP slot reflected.
  - Nightwalker: +1 Evasion reflected.
  - Stalwart: cumulative threshold bonuses reflected.
  - Winged Sentinel: Severe +4 reflected.
  - Vengeance: Stress = 7 reflected.
  - Subclasses without `effects`: no unintended numeric changes.

- **Ancestry Effects Application**
  - Clank: after adding 2 experiences, assign bonus to one; it shows +3 in builder; viewer mirrors it.
  - Galapa: thresholds +2 applied.
  - Giant: HP +1 applied.
  - Human: Stress +1 applied (6 → 7).
  - Simiah: Evasion +1 applied.
  - Earthkin (Playtest): Armor +1 and thresholds +1; playtest label visible.

- **Community Display and Playtest Labels**
  - For all communities: feature text displays on selection (builder) and viewer; playtest communities show label.

- **Trait Assignment Validation**
  - Distribution enforcement of {-1, 0, 0, +1, +1, +2}; invalid sets blocked from completion.
  - Evasion updates with Agility and includes ancestry/subclass modifiers.

- **Equipment Selection Flow**
  - Primary/Secondary weapons: single selection per slot; replacement works; suggested equipment action fills correctly.
  - Armor: single selection enforced; replacement works.
  - Starting inventory gating: chooseOne and chooseExtra must be satisfied if present.
  - Item alias mapping: selecting aliases satisfies gating, e.g.:
    - "minor healing potion" → "minor health potion"
    - "healing potion" → "health potion"
    - "major healing potion" → "major health potion"
  - Removal/clear-all updates progress and computed stats.

- **Experience Creation and Editing**
  - Add up to 2 experiences; 3rd blocked; blank name rejected.
  - Edit/save description; viewer reflects change.
  - Clank bonus integration honors selected experience as +3.

- **Domain Card Selection**
  - Only class domain, level 1 cards appear; selection limit enforced (2 or 3 with subclass bonus).
  - Toggle deselect works; viewer shows merged ability data.
  - Ability metadata validation: each selected card shows `recallCost` and `type` consistent with `abilities.json`.

- **Background and Connections**
  - Background: at least one answered marks complete; manual mark complete shows success notice.
  - Connections: at least one non-empty answer required.

- **Character Info & Persistence**
  - Name/pronouns autosave and dispatch events; persisted to DB.
  - Profile image upload stores to S3; signed URL shown; clear resets and deletes.
  - Final save blocked until all steps complete; success emits share URL and key.

- **Viewer Rendering and Consistency**
  - Computed stats (evasion, hp, stress, thresholds, armor score) match builder.
  - Ancestry bonus breakdown aligns with builder values.
  - Class/subclass/ancestry/community names and features render; playtest labels on playtest content.
  - Equipment organized under weapons/armor/items/consumables.
  - Weapon feature text normalization handles strings/arrays/objects.
  - Domain cards include ability data.
  - Experiences show two entries; Clank target shows +3, others +2.

- **Accessibility and UX**
  - Keyboard navigation across steps/grids.
  - ARIA labels on interactive elements.
  - Responsive card layouts on mobile/tablet/desktop.

- **Data Integrity and SRD Compliance**
  - Class-domain mapping equals JSON/SRD.
  - Starting stats equal base + modifiers.
  - Class Hope features cost 3 where surfaced.
  - Ability `recallCost` values match `abilities.json`; starting selections are level 1.
  - Playtest content shows the required label.

### Known data-to-implementation drift to detect (must fail loudly)

- Sorcerer domains must be Arcana + Midnight (SRD). If implementation maps Sorcerer to Arcana + Splendor, the class→domain test should fail.
- Newer classes in `classes.json` (e.g., Witch, Warlock, Brawler, Assassin) must have domain filtering and selection limits applied. If the implementation uses a hard-coded domain map that omits these, tests should fail on domain card filtering for these classes.

---

If needed next: expand each bullet into Given/When/Then for direct Pest scenarios.

### Expansion — Given/When/Then Outlines (Key Cases)

- Clank Experience Bonus
  - Given a new character with ancestry Clank and two experiences named X and Y
  - When I assign the Clank bonus to experience X in the builder
  - Then experience X shows +3 and Y shows +2 in both builder and viewer

- School of Knowledge Domain Card Bonus
  - Given a character with a class that has domains and subclass “School of Knowledge”
  - When I select domain cards
  - Then I can select 3 total cards; the 3rd selection is allowed and persists to viewer

- School of War Hit Point Bonus
  - Given a character with subclass “School of War”
  - When I view computed stats
  - Then hit points show base +1 relative to class base and other bonuses

- Nightwalker Evasion Bonus
  - Given a character with subclass “Nightwalker”
  - When I view computed stats
  - Then evasion shows +1 relative to class base + agility + other bonuses

- Stalwart Damage Threshold Bonuses
  - Given a character with subclass “Stalwart”
  - When I view computed stats for thresholds
  - Then thresholds include the sum of +1 (foundation), +2 (spec), +3 (mastery) as applicable

- Winged Sentinel Severe Threshold Bonus
  - Given a character with subclass “Winged Sentinel”
  - When I view computed stats
  - Then severe threshold includes +4 bonus

- Vengeance Stress Bonus
  - Given a character with subclass “Vengeance”
  - When I view computed stats
  - Then stress slots are 7 (6 base +1)

- Galapa Damage Threshold = Proficiency
  - Given a character with ancestry “Galapa” (level 1 proficiency = 2)
  - When I view computed stats
  - Then thresholds include +2 bonus

- Giant Hit Point Bonus
  - Given a character with ancestry “Giant”
  - When I view computed stats
  - Then hit points include +1 bonus

- Human Stress Bonus
  - Given a character with ancestry “Human”
  - When I view computed stats
  - Then stress slots are 7 (6 base +1)

- Simiah Evasion Bonus
  - Given a character with ancestry “Simiah”
  - When I view computed stats
  - Then evasion includes +1 bonus

- Earthkin Playtest Armor/Threshold Bonus
  - Given a character with ancestry “Earthkin” (playtest)
  - When I view computed stats and ancestry display
  - Then armor score and thresholds include +1; playtest label is visible

- Domain Card Filtering and Selection Limits
  - Given a selected class
  - When I view domain cards
  - Then only level 1 cards from the class’s two domains appear; selection capped at 2 (or 3 with subclass bonus)

- Abilities Cross-Reference (All Levels)
  - Given `domains.json` and `abilities.json`
  - When I iterate each domain and levels 1–10
  - Then each listed ability exists in `abilities.json` with matching domain and level; types and recallCost are present; descriptions non-empty

- Playtest Ability Labeling
  - Given an ability with `playtest.isPlaytest = true`
  - When I view the ability details in the viewer
  - Then the “Void - Playtest vX.X” label is visible

- Touched Cards at Level 7
  - Given each domain at level 7
  - When I check for the presence of the domain’s “touched” card
  - Then the card exists with correct domain, level, type, recallCost, and description

- Trait Assignment Enforcement
  - Given the traits step
  - When I assign values not equal to {-1, 0, 0, +1, +1, +2}
  - Then the step is not complete; correcting to exact multiset completes the step

- Starting Equipment Gating
  - Given the equipment step
  - When I select primary weapon, armor, and satisfy chooseOne/chooseExtra where required
  - Then the equipment step becomes complete; removing required items reverts it

- Experiences Add/Edit Limits
  - Given the experiences step
  - When I add two experiences and attempt a third
  - Then the third is blocked; editing a description persists to viewer

- Background/Connections Completion
  - Given background and connections steps
  - When I answer at least one background question and one connection
  - Then both steps show complete; manual background completion shows success message

- Persistence & Events
  - Given a character name/pronouns change
  - When I type into the fields
  - Then autosave occurs and the last saved timestamp updates; events dispatched

- Profile Image Upload/Clear
  - Given a supported image file
  - When I upload the profile image
  - Then an S3 path is saved and a temporary URL is used; clearing removes the image and updates viewer



