# DaggerHeart Character Leveling System

## Overview

The DaggerHeart leveling system allows characters to advance through 10 levels divided into 4 tiers. Each level-up consists of three phases: **Tier Achievements**, **Advancements**, and **Damage Threshold Updates**.

## Tier Structure

- **Tier 1**: Level 1 only
- **Tier 2**: Levels 2-4  
- **Tier 3**: Levels 5-7
- **Tier 4**: Levels 8-10

## Level-Up Process

### Step One: Tier Achievements (Automatic)

These are automatic benefits gained at specific milestone levels:

#### Level 2 (Tier 2 Entry)
- Gain a new Experience at +2 modifier
- Permanently increase Proficiency by +1

#### Level 5 (Tier 3 Entry)  
- Gain a new Experience at +2 modifier
- Permanently increase Proficiency by +1
- Clear all marked character traits (allows trait advancement again)

#### Level 8 (Tier 4 Entry)
- Gain a new Experience at +2 modifier
- Permanently increase Proficiency by +1
- Clear all marked character traits (allows trait advancement again)

### Step Two: Advancements (Player Choice)

Players choose **exactly 2 advancements** from their current tier or any lower tier. Each advancement can only be selected a limited number of times as specified.

#### Available Advancement Options

##### Trait Bonuses
- **Description**: "Gain a +1 bonus to two unmarked character traits and mark them"
- **Max Selections**: 3 (across all tiers)
- **Restrictions**: Can't increase marked traits until next tier clears them
- **User Input Required**: Select which 2 traits to increase

##### Hit Point Enhancement  
- **Description**: "Permanently gain one Hit Point slot"
- **Max Selections**: 2 per tier
- **Effect**: Increases maximum hit points by 1

##### Stress Enhancement
- **Description**: "Permanently gain one Stress slot"  
- **Max Selections**: 2 per tier
- **Effect**: Increases maximum stress by 1

##### Experience Bonus
- **Description**: "Permanently gain a +1 bonus to two Experiences"
- **Max Selections**: 1 per tier
- **User Input Required**: Select which 2 existing experiences to enhance

##### Domain Card Acquisition
- **Description**: "Choose an additional domain card of your level or lower from a domain you have access to"
- **Max Selections**: 1 per tier
- **Level Limits**: 
  - Tier 2: Up to level 4 cards
  - Tier 3: Up to level 7 cards  
  - Tier 4: Any level cards
- **User Input Required**: Select domain card from available options

##### Evasion Bonus
- **Description**: "Permanently gain a +1 bonus to your Evasion"
- **Max Selections**: 1 per tier
- **Effect**: Increases Evasion by +1

##### Subclass Advancement (Tier 3+ Only)
- **Description**: "Take an upgraded subclass card. Then cross out the multiclass option for this tier"
- **Max Selections**: 1 per tier
- **Mutual Exclusivity**: Cannot select if Multiclass chosen in same tier
- **User Input Required**: Choose next subclass card progression

##### Proficiency Increase (Special - Costs 2 Advancement Slots)
- **Description**: "Increase your Proficiency by +1"
- **Max Selections**: 1 per tier
- **Special Rule**: Requires both advancement slots for the tier
- **Effect**: +1 to Proficiency and +1 damage die to weapons

##### Multiclass Selection (Tier 3+ Only, Special - Costs 2 Advancement Slots)
- **Description**: "Choose an additional class for your character, then cross out an unused 'Take an upgraded subclass card' and the other multiclass option on this sheet"
- **Max Selections**: 2 total (across Tier 3 and 4)
- **Special Rules**: 
  - Requires both advancement slots for the tier
  - Mutually exclusive with Subclass advancement in same tier
  - Gains class feature and foundation subclass card
  - Access to one domain from chosen class
- **User Input Required**: Select additional class and domain

### Step Three: Damage Thresholds (Automatic)

All damage thresholds increase by +1. This affects:
- Major damage threshold
- Severe damage threshold

### Step Four: Domain Cards (Automatic)

Acquire a new domain card at character level or lower from class domains and add to loadout or vault. Players can also exchange existing cards for different ones of same/lower level.

## Implementation Requirements

### Data Storage

Character advancements are stored in the `character_advancements` table with:
- `character_id`: Link to character
- `tier`: Which tier (1-4) 
- `advancement_number`: Which slot (1-2) within the tier
- `advancement_type`: Type of advancement selected
- `advancement_data`: JSON data for advancement specifics
- `description`: Human-readable description

### Validation Rules

1. **Tier Progression**: Character must be appropriate level for tier
2. **Slot Availability**: Each tier has exactly 2 advancement slots
3. **Selection Limits**: Respect `maxSelections` limits per advancement type
4. **Mutual Exclusivity**: Subclass and Multiclass cannot be chosen in same tier
5. **Special Costs**: Proficiency and Multiclass cost 2 slots each
6. **Trait Marking**: Cannot advance marked traits until tier achievement clears them

### User Interface Flow

1. **Level Up Trigger**: Available when character reaches new level
2. **Tier Achievement Display**: Show automatic benefits gained
3. **Advancement Selection**: 
   - Display available options with selection limits
   - Show requirements for user input (trait selection, etc.)
   - Prevent invalid combinations
   - Track selections across both slots
4. **Confirmation**: Review all selections before applying
5. **Application**: Apply all changes atomically

### Integration Points

- **Character Viewer**: Display level up button when eligible
- **Character Stats**: Include advancement bonuses in computed stats
- **Domain Cards**: Integrate with domain card selection system
- **Experience System**: Allow modification of existing experiences
- **Equipment System**: Handle proficiency increases affecting weapon damage

## UI Components Needed

1. **Level Up Modal**: Main interface for level-up process
2. **Tier Achievement Display**: Show automatic benefits
3. **Advancement Selection Cards**: Selectable advancement options
4. **Trait Selector**: For trait bonus selections
5. **Experience Selector**: For experience bonus selections
6. **Domain Card Selector**: For domain card acquisitions
7. **Class Selector**: For multiclass selections
8. **Confirmation Screen**: Review all selections
9. **Progress Indicators**: Show advancement slot usage

## Testing Requirements

1. **Advancement Limits**: Verify selection limits are enforced
2. **Mutual Exclusivity**: Test subclass/multiclass exclusion
3. **Special Costs**: Verify 2-slot advancements work correctly
4. **Tier Progression**: Test level requirements for tiers
5. **Stat Integration**: Verify bonuses apply to character stats
6. **Persistence**: Test saving and loading advancement data
7. **Edge Cases**: Invalid combinations, insufficient levels, etc.
