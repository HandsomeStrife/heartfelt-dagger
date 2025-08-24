# DaggerHeart Classes and Subclasses Audit

This document provides a comprehensive overview of all classes and subclasses in DaggerHeart, analyzing their mechanical features and potential implementation issues.

## Classes Overview

DaggerHeart has 9 classes, each with exactly 2 domains and 2 subclasses:

| Class | Domains | Starting Evasion | Starting Hit Points | Subclasses |
|-------|---------|------------------|---------------------|------------|
| Bard | Grace + Codex | 10 | 5 | Troubadour, Wordsmith |
| Druid | Sage + Arcana | 10 | 6 | Warden of the Elements, Warden of Renewal |
| Guardian | Valor + Blade | 9 | 7 | Stalwart, Vengeance |
| Ranger | Bone + Sage | 12 | 6 | Beastbound, Wayfinder |
| Rogue | Midnight + Grace | 12 | 6 | Nightwalker, Syndicate |
| Seraph | Splendor + Valor | 9 | 7 | Divine Wielder, Winged Sentinel |
| Sorcerer | Arcana + Midnight | 10 | 6 | Elemental Origin, Primal Origin |
| Warrior | Blade + Bone | 11 | 6 | Call of the Brave, Call of the Slayer |
| Wizard | Codex + Splendor | 11 | 5 | School of Knowledge, School of War |

---

## Class Details

### 1. BARD (Grace + Codex)
- **Starting Stats**: Evasion 10, Hit Points 5
- **Hope Feature**: Make a Scene (3 Hope) - Temporarily Distract target within Close range, -2 penalty to their Difficulty
- **Class Features**: 
  - Rally (once per session, give Rally Die to self and allies - d6 at level 1, d8 at level 5)
- **Subclasses**:

#### Troubadour (Presence spellcasting)
- **Foundation**: Gifted Performer - 3 song types once per long rest
- **Specialization**: Maestro - Rally Die gives Hope or clears Stress
- **Mastery**: Virtuoso - Use each song twice per long rest

#### Wordsmith (Presence spellcasting)  
- **Foundation**: Rousing Speech (clear 2 Stress on allies), Heart of a Poet (+d4 to social rolls)
- **Specialization**: Eloquent - Enhanced ally encouragement options
- **Mastery**: Epic Poetry - Rally Die becomes d10, enhanced Help Ally

---

### 2. DRUID (Sage + Arcana)
- **Starting Stats**: Evasion 10, Hit Points 6
- **Hope Feature**: Evolution (3 Hope) - Transform to Beastform without Stress, +1 trait bonus
- **Class Features**:
  - Beastform (Mark Stress to transform into creature)
  - Wildtouch (harmless nature effects at will)
- **Subclasses**:

#### Warden of the Elements (Instinct spellcasting)
- **Foundation**: Elemental Incarnation - Channel elements (Fire/Earth/Water/Air) 
- **Specialization**: Elemental Aura - Area effects matching channeled element
- **Mastery**: Elemental Dominion - Enhanced elemental benefits

#### Warden of Renewal (Instinct spellcasting)
- **Foundation**: Clarity of Nature (stress clearing sanctuary), Regeneration (3 Hope, clear 1d4 HP)
- **Specialization**: Regenerative Reach (extend range), Warden's Protection (2 Hope, heal multiple allies)
- **Mastery**: Defender - Reduce ally damage while in Beastform

---

### 3. GUARDIAN (Valor + Blade)
- **Starting Stats**: Evasion 9, Hit Points 7
- **Hope Feature**: Frontline Tank (3 Hope) - Clear 2 Armor Slots
- **Class Features**:
  - Unstoppable (once per long rest, gain escalating die for damage reduction and bonus damage)
- **Subclasses**:

#### Stalwart
- **Foundation**: Unwavering (+1 damage thresholds), Iron Will (extra armor slot usage)
- **Specialization**: Unrelenting (+2 damage thresholds), Partners-in-Arms (protect allies)
- **Mastery**: Undaunted (+3 damage thresholds), Loyal Protector (take ally damage)
- **MECHANICAL BONUSES**: Total +6 damage threshold bonus at mastery

#### Vengeance  
- **Foundation**: At Ease (+1 stress slot), Revenge (2 Stress to damage attacker)
- **Specialization**: Act of Reprisal (+1 proficiency vs enemies who damage allies)
- **Mastery**: Nemesis (2 Hope to prioritize enemy, swap dice vs them)
- **MECHANICAL BONUSES**: +1 stress slot

---

### 4. RANGER (Bone + Sage)
- **Starting Stats**: Evasion 12, Hit Points 6
- **Hope Feature**: Hold Them Off (3 Hope) - Use attack roll against 2 additional targets
- **Class Features**:
  - Ranger's Focus (Hope to mark target, gain tracking/damage bonuses)
  - Companion (shared growth with animal companion)
- **Subclasses**:

#### Beastbound (Agility spellcasting)
- **Foundation**: Companion (animal companion with shared advancement)
- **Specialization**: Expert Training (+companion options), Battle-Bonded (+2 Evasion when companion near)
- **Mastery**: Advanced Training (2 more companion options), Loyal Friend (damage swapping)

#### Wayfinder (Agility spellcasting)
- **Foundation**: Ruthless Predator (Stress for +1 proficiency, severe damage marks stress), Path Forward (navigation ability)
- **Specialization**: Elusive Predator (+2 Evasion vs Focus attacks)  
- **Mastery**: Apex Predator (Hope to remove Fear from GM pool on successful Focus attack)

---

### 5. ROGUE (Midnight + Grace)
- **Starting Stats**: Evasion 12, Hit Points 6
- **Hope Feature**: Rogue's Dodge (3 Hope) - +2 Evasion until hit or rest
- **Class Features**:
  - Cloaked (enhanced Hidden condition)
  - Sneak Attack (tier-based d6s when Cloaked or flanking)
- **Subclasses**:

#### Nightwalker (Finesse spellcasting)
- **Foundation**: Shadow Stepper (Stress to teleport between shadows, gain Cloaked)
- **Specialization**: Dark Cloud (create concealing darkness), Adrenaline (+level damage while Vulnerable)
- **Mastery**: Fleeting Shadow (+1 Evasion permanently, extend teleport range), Vanishing Act (Stress for Cloaked)
- **MECHANICAL BONUSES**: +1 Evasion permanently

#### Syndicate (Finesse spellcasting)
- **Foundation**: Well-Connected (contacts in every town)
- **Specialization**: Contacts Everywhere (session resource for help)
- **Mastery**: Reliable Backup (3x contacts per session, more options)

---

### 6. SERAPH (Splendor + Valor)
- **Starting Stats**: Evasion 9, Hit Points 7
- **Hope Feature**: Life Support (3 Hope) - Clear ally Hit Point within Close range
- **Class Features**:
  - Prayer Dice (session resource, d4s equal to spellcast trait)
- **Subclasses**:

#### Divine Wielder (Strength spellcasting)
- **Foundation**: Spirit Weapon (ranged weapon attacks, Stress for multi-target), Sparing Touch (clear 2 HP or Stress once per rest)
- **Specialization**: Devout (extra Prayer Die, use Sparing Touch twice)
- **Mastery**: Sacred Resonance (matching damage dice double in value)

#### Winged Sentinel (Strength spellcasting)
- **Foundation**: Wings of Light (flight, Stress to carry others, Hope for +1d8 damage)
- **Specialization**: Ethereal Visage (advantage on Presence while flying)
- **Mastery**: Ascendant (+4 severe damage threshold), Power of the Gods (+1d12 damage instead of 1d8)
- **MECHANICAL BONUSES**: +4 severe damage threshold bonus

---

### 7. SORCERER (Arcana + Midnight) 
- **Starting Stats**: Evasion 10, Hit Points 6
- **Hope Feature**: Volatile Magic (3 Hope) - Reroll any magic damage dice
- **Class Features**:
  - Arcane Sense (detect magic within Close range)
  - Minor Illusion (Spellcast 10 for visual illusion)
  - Channel Raw Power (once per rest, vault card for Hope or damage bonus)
- **Subclasses**:

#### Elemental Origin (Instinct spellcasting)
- **Foundation**: Elementalist (choose element, Hope for +2/+3 bonus)
- **Specialization**: Natural Evasion (Stress + d6 to Evasion when attacked)
- **Mastery**: Transcendence (transformation with multiple benefit options)

#### Primal Origin (Instinct spellcasting)
- **Foundation**: Manipulate Magic (Stress to modify spell effects - range/bonus/double die/multi-target)
- **Specialization**: Enchanted Aid (d8 advantage die when helping spellcasters)
- **Mastery**: Arcane Charge (2 Hope or taking magic damage to become Charged for big bonuses)

---

### 8. WARRIOR (Blade + Bone)
- **Starting Stats**: Evasion 11, Hit Points 6  
- **Hope Feature**: No Mercy (3 Hope) - +1 attack rolls until rest
- **Class Features**:
  - Attack of Opportunity (reaction roll when enemies leave melee)
  - Combat Training (ignore weapon burden, +level to physical damage)
- **Subclasses**:

#### Call of the Brave
- **Foundation**: Courage (Hope on failed Fear rolls), Battle Ritual (clear 2 Stress, gain 2 Hope before danger)
- **Specialization**: Rise to the Challenge (d20 Hope Die when at 2 or fewer HP)
- **Mastery**: Camaraderie (extra Tag Team Roll, allies spend 2 Hope instead of 3)

#### Call of the Slayer
- **Foundation**: Slayer (Hope Die pool system for attack/damage bonuses)
- **Specialization**: Weapon Specialist (Hope for secondary weapon damage, reroll 1s on Slayer Dice)
- **Mastery**: Martial Preparation (party gets Slayer Dice during rest)

---

### 9. WIZARD (Codex + Splendor)
- **Starting Stats**: Evasion 11, Hit Points 5
- **Hope Feature**: Not This Time (3 Hope) - Force adversary reroll within Far range
- **Class Features**:
  - Prestidigitation (harmless magical effects at will)
  - Strange Patterns (choose 1-12, gain Hope/clear Stress on that Duality Die result)
- **Subclasses**:

#### School of Knowledge (Knowledge spellcasting) ⚠️ **RECENTLY FIXED**
- **Foundation**: Prepared (Hope for +3 Knowledge bonus), Adept (Stress instead of Hope for doubled Experience)
- **Specialization**: Accomplished (+1 domain card), Perfect Recall (reduce domain card costs)
- **Mastery**: Brilliant (declare obscure knowledge), Honed Expertise (free Experience use on 5-6)
- **MECHANICAL BONUSES**: +1 domain card (CORRECTED from +3)

#### School of War (Knowledge spellcasting)
- **Foundation**: Battlemage (+1 Hit Point), Face Your Fear (+1d10 magic damage on Fear success)
- **Specialization**: Conjure Shield (proficiency to Evasion with 2+ Hope), Fueled by Fear (2d10 magic damage)
- **Mastery**: Thrive in Chaos (Stress to force extra HP damage), Have No Fear (3d10 magic damage)
- **MECHANICAL BONUSES**: +1 Hit Point

---

## Potential Issues to Investigate

### Domain Card Bonuses
- ✅ **School of Knowledge**: Fixed - now provides +1 domain card (not +3)
- ❓ **Other subclasses**: Need to verify no other subclasses incorrectly provide domain card bonuses

### Mechanical Bonus Stacking
- **Stalwart Guardian**: +6 damage threshold total (+1+2+3)
- **Winged Sentinel Seraph**: +4 severe damage threshold
- **School of War Wizard**: +1 Hit Point
- **Vengeance Guardian**: +1 Stress slot  
- **Nightwalker Rogue**: +1 Evasion
- Need to verify these stack correctly in character calculations

### Spellcast Traits
Each subclass should specify their spellcasting trait for Prayer Dice calculation:
- **Troubadour/Wordsmith**: Presence
- **Warden subclasses**: Instinct  
- **Divine Wielder/Winged Sentinel**: Strength
- **Nightwalker/Syndicate**: Finesse
- **Elemental/Primal Origin**: Instinct
- **School subclasses**: Knowledge
- **Beastbound/Wayfinder**: Agility

### Starting Equipment Validation
Need to verify suggested equipment exists in corresponding JSON files:
- All suggested weapons exist in weapons.json
- All suggested armor exists in armor.json  
- All starting inventory items exist in items.json/consumables.json

### Class Feature Implementation
Need to verify complex features are properly implemented:
- **Bard Rally Dice**: Progression from d6 to d8 at level 5
- **Guardian Unstoppable**: Die value progression and removal mechanics
- **Rogue Sneak Attack**: Proper tier-based d6 scaling
- **Seraph Prayer Dice**: Proper spellcast trait calculation
- **Warrior Combat Training**: Level-based damage bonus

---

## Testing Strategy

Each class/subclass combination needs comprehensive testing to verify:

1. **Character Creation**:
   - Correct starting stats (Evasion, Hit Points)
   - Proper domain assignment
   - Suggested equipment availability
   - Starting inventory validation

2. **Subclass Selection**:
   - Proper feature descriptions display
   - Mechanical bonuses apply correctly
   - Domain card limits adjust appropriately
   - Spellcast trait assignment

3. **Progression Mechanics**:
   - Level-based feature scaling
   - Tier advancement options
   - Feature interaction validation

4. **UI/UX Validation**:
   - All text displays correctly
   - No missing descriptions
   - Proper bonus calculation display
   - Equipment suggestions work

This audit reveals that the recent School of Knowledge fix was necessary and suggests a systematic approach to testing each class/subclass for similar issues.
