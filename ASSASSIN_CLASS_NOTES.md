# Assassin Class - DaggerHeart Playtest v1.5

## Basic Information
- **Class Name**: Assassin
- **Domains**: Midnight & Blade
- **Playtest Version**: v1.5 (Void - Playtest v1.5)
- **Starting Evasion**: 12 (high mobility/stealth focus)

## Class Features (Page 1)

### Core Class Features

#### 1. **Marked for Death**
- On a successful weapon attack, you can **mark a Stress** to make the target **Marked for Death**
- Attacks against **Marked for Death** targets gain bonus damage equal to **+1d4 per tier**
- You can only have **one adversary Marked for Death** at a time
- Cannot transfer or remove the condition except by defeating the target
- The GM can spend a number of **Fear equal to your Proficiency** to remove the **Marked for Death** condition
- Otherwise, it ends automatically when you take a rest

#### 2. **Get In & Get Out**
- **Spend a Hope** to ask the GM for either a **quick** or **inconspicuous way** into or out of a building or structure you can see
- The next roll you make that capitalizes on this information has **advantage**

### Hope Feature
- **Grim Resolve**: Spend **3 Hope** to clear **2 Stress**

## Key Mechanics Observed
- **High Evasion Start**: 12 starting evasion suggests strong defensive capabilities
- **Stealth & Infiltration Focus**: "Get In & Get Out" emphasizes reconnaissance and infiltration
- **Single-Target Damage**: "Marked for Death" provides escalating damage against priority targets
- **Stress Management**: Hope feature helps manage stress accumulation from stealth activities

## Domain Synergy
- **Midnight Domain**: Stealth, shadows, illusion, and concealment magic
- **Blade Domain**: Weapon mastery and combat prowess

## Design Philosophy
The Assassin appears to be designed as a **single-target specialist** with strong **infiltration** and **stealth** capabilities. The high starting evasion (12) combined with Midnight domain access suggests exceptional survivability through avoidance rather than damage absorption.

## Page 2 Details

### Suggested Traits
- **+2 Agility** (primary trait - stealth and mobility)
- **+1 Strength, +1 Finesse** (combat effectiveness)
- **+1 Knowledge** (tactical planning)
- **+0 Instinct, +0 Presence** (less social/intuitive focus)

### Suggested Equipment

#### Primary Weapon
- **Broadsword**: Agility Melee, d8 phy damage, One-Handed
- **Reliable**: +1 to attack rolls

#### Secondary Weapon  
- **Short Sword**: Agility Melee, d8 phy damage, One-Handed
- **Paired**: +2 to primary weapon damage to targets within Melee range

#### Armor
- **Leather Armor**: Thresholds 6/13, Score 3

### Starting Inventory
- **Always**: torch, 50 feet of rope, basic supplies, handful of gold
- **Choose One**: Minor Health Potion OR Minor Stamina Potion
- **Choose One**: list of names with sins marked off OR mortal and pestle inscribed with mysterious insignia

### Background Questions
1. What organization trained you in the art of killing, and how did you gain membership into it?
2. Throughout your entire career, one target has eluded you. Who are they, and how have they managed to slip through your fingers?
3. You always do what you must to take down your target, but there's one line that you will never cross. What is it?

### Connections
1. What about me frightens you?
2. You once asked me to do something that keeps you up at night. What was it?
3. What secret about myself did I tell you, and how did it change your view of me?

### Tier Advancement Options
Standard DaggerHeart tier progression (2-4, 5-7, 8-10) with:
- Character trait bonuses
- Hit Point/Stress slot increases  
- Experience bonuses
- Domain card selection
- Evasion bonuses
- Subclass upgrades
- Proficiency increases
- Multiclass options

## Subclass Details

### Executioners Guild (Combat Specialist)
**Spellcast Trait**: Agility

**Foundation Features:**
- **First Strike**: The first time in a scene you succeed on an attack roll, double the damage of the attack
- **Ambush**: Your "Marked for Death" feature uses d6s instead of d4s

**Specialization Features:**  
- **Death Strike**: When you deal severe damage to a creature, you can mark a Stress to make them mark an additional Hit Point
- **Scorpion's Poise**: You gain a +2 bonus to your Evasion against any attacks made by a creature Marked for Death

**Mastery Features:**
- **True Strike**: Once per long rest, when you fail an attack roll, you can spend a Hope to make it a success instead  
- **Backstab**: Your "Marked for Death" feature uses d8s instead of d6s

### Poisoners Guild (Toxin Specialist)
**Spellcast Trait**: Knowledge

**Foundation Features:**
- **Toxic Concoctions**: Mark a Stress to add 1d4+1 toxins to this card. On your next long rest, clear this card. You know these poisons:
  - **Beguile Toxin**: Target gains -1 penalty to their Difficulty (once per target)
  - **Grave Mold**: Gain +1d6 damage bonus on this attack
  - **Leech Weed**: Gain +1d6 damage bonus on this attack
  - **Envenomate**: Spend a token to afflict target with a known poison's effect on successful weapon attack

**Specialization Features:**
- **Poison Compendium**: You know these additional poisons:
  - **Midnight's Veil**: Target gains permanent -2 penalty to attack rolls (once per target)
  - **Ghost Petal**: Permanently decrease target's damage dice by one step (d10→d8→d6, once per target)
  - **Adder's Blessing**: You are immune to poisons and other toxins

**Mastery Features:**
- **Venomancer**: You know these advanced poisons:
  - **Blight Seed**: Target gains permanent -3 penalty to damage threshold (once per target)
  - **Fear Leaf**: Attack gains damage bonus equal to Fear Die result
  - **Twin Fang**: When afflicting with poison, spend additional token to inflict a second known poison

## Design Philosophy Analysis

### Executioners Guild
Focuses on **pure combat effectiveness** and **burst damage**:
- Escalates "Marked for Death" from d4s → d6s → d8s 
- Guarantees critical opening strikes with "First Strike"
- Provides defensive bonuses against marked targets
- Offers reliability with "True Strike"

### Poisoners Guild  
Creates a **debuff/control specialist** playstyle:
- Resource management through toxin tokens
- Permanent debuffs that stack over encounters
- Knowledge-based spellcasting rather than Agility
- Tactical battlefield control through status effects

## Notes for Implementation
- Still need: Starting Hit Points (estimate 4-5 based on high Evasion)
- Subclass progression: Foundation → Specialization → Mastery (standard)
- Poison mechanics may need special UI considerations for token tracking

## Status
- [x] Page 1 documentation complete
- [x] Page 2 documentation complete
- [x] Subclass information complete
- [ ] JSON implementation pending
- [ ] Testing pending
