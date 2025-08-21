# DaggerHeart Campaigns & Rooms System

## Project Overview

This document outlines the development plan for adding **Campaigns** and **Rooms** features to the DaggerHeart Character Builder platform. These features will enable users to create collaborative gaming experiences with persistent campaigns and real-time video chat rooms for live sessions.

## Feature 1: Campaigns System

### Core Functionality

#### Campaign Creation
- **Creator Requirements**: Must be logged in
- **Campaign Data**:
  - Name (required, max 100 characters)
  - Description (required, max 1000 characters)
  - Created timestamp
  - Creator user ID
  - Unique invite code (8-character alphanumeric)
  - Status (active, archived, etc.)

#### Campaign Management
- **Campaign Page**: Displays campaign name, description, creator info
- **Member Management**: Shows all joined players with their characters
- **Invite System**: Shareable link using unique invite code
- **Access Control**: Only campaign creator can manage settings

#### Player Participation
- **Join Process**: Click invite link → login required → character selection
- **Character Selection Options**:
  - Choose from existing characters
  - Join with "empty" character (placeholder)
- **Character Display**: Show character name, class, subclass, ancestry, community, player username

#### User Views
- **My Campaigns**: List of campaigns user created
- **Joined Campaigns**: List of campaigns user has joined
- **Campaign Dashboard**: Central hub showing both lists

### Technical Architecture

#### Domain Structure
```
domain/Campaign/
├── Models/
│   ├── Campaign.php
│   └── CampaignMember.php
├── Data/
│   ├── CampaignData.php
│   ├── CampaignMemberData.php
│   └── CreateCampaignData.php
├── Actions/
│   ├── CreateCampaignAction.php
│   ├── JoinCampaignAction.php
│   └── LeaveCampaignAction.php
├── Repositories/
│   └── CampaignRepository.php
└── Enums/
    └── CampaignStatus.php
```

#### Database Schema
```sql
-- campaigns table
id, name, description, creator_id, invite_code, status, created_at, updated_at

-- campaign_members table
id, campaign_id, user_id, character_id (nullable), joined_at, created_at, updated_at
```

#### Key Routes
- `GET /campaigns` - Campaign dashboard
- `GET /campaigns/create` - Create campaign form
- `POST /campaigns` - Store new campaign
- `GET /campaigns/{campaign}` - Campaign detail page
- `GET /campaigns/join/{invite_code}` - Join campaign via invite
- `POST /campaigns/{campaign}/join` - Process join request

## Feature 2: Rooms System

### Core Functionality

#### Room Creation
- **Creator Requirements**: Must be logged in
- **Room Data**:
  - Name (required, max 100 characters)
  - Description (required, max 500 characters)
  - Password (required, for access control)
  - Guest count (1-5, determines layout)
  - Unique invite link
  - Created timestamp
  - Creator user ID

#### Room Layouts
- **1 Guest**: Single large video slot
- **2 Guests**: Side-by-side layout
- **3 Guests**: Triangle arrangement
- **4 Guests**: 2x2 grid
- **5 Guests**: 2x3 grid (with host taking center slot)

#### Guest Participation
- **Join Process**: Click invite link → login required → character selection → enter room
- **Character Options**:
  - Select existing character
  - Create temporary character (name + class only)
- **Video Integration**: Leverages existing WebRTC functionality

#### Video Integration
- **Base Technology**: Extend existing `webrtc-rooms.js` functionality
- **Character Overlay**: Show character info on video feeds
- **Slot Management**: Dynamic slot allocation based on guest count
- **Room State**: Persistent connection management

### Technical Architecture

#### Domain Structure
```
domain/Room/
├── Models/
│   ├── Room.php
│   └── RoomParticipant.php
├── Data/
│   ├── RoomData.php
│   ├── RoomParticipantData.php
│   └── CreateRoomData.php
├── Actions/
│   ├── CreateRoomAction.php
│   ├── JoinRoomAction.php
│   └── LeaveRoomAction.php
├── Repositories/
│   └── RoomRepository.php
└── Enums/
    └── RoomStatus.php
```

#### Database Schema
```sql
-- rooms table
id, name, description, password, guest_count, creator_id, invite_code, status, created_at, updated_at

-- room_participants table
id, room_id, user_id, character_id (nullable), character_name (nullable), character_class (nullable), joined_at, left_at (nullable)
```

#### Key Routes
- `GET /rooms` - Room dashboard
- `GET /rooms/create` - Create room form
- `POST /rooms` - Store new room
- `GET /rooms/{room}` - Room detail/lobby page
- `GET /rooms/join/{invite_code}` - Join room via invite
- `POST /rooms/{room}/join` - Process join request
- `GET /rooms/{room}/session` - Live video room session

## Integration Points

### Existing Systems Integration

#### Character System
- **Character Selection**: Integrate with existing character repository
- **Character Display**: Use existing character data structure
- **Character Creation**: Link to existing character builder for empty characters

#### User Authentication
- **Login Requirements**: All features require authenticated users
- **User Association**: Link campaigns/rooms to user accounts
- **Permission Checks**: Ensure proper access control

#### Video System Enhancement
- **WebRTC Foundation**: Build upon existing `webrtc-rooms.js`
- **Character Overlay**: Extend existing character display system
- **Dynamic Layouts**: Modify slot system for variable guest counts
- **State Management**: Enhance Ably integration for room-specific channels

### UI/UX Integration

#### Dashboard Integration
- **Navigation**: Add campaigns/rooms to main navigation
- **Quick Actions**: Update dashboard cards with proper links
- **Status Indicators**: Show active campaigns/rooms count

#### Design Consistency
- **Theme Alignment**: Use existing DaggerHeart color scheme
- **Component Reuse**: Leverage existing UI components
- **Fantasy Aesthetic**: Maintain fantasy theme in new interfaces

## Development Phases

### Phase 1: Foundation (Campaigns Basic)
1. Create domain structure for campaigns
2. Database migrations and models
3. Basic CRUD operations
4. Campaign creation and listing pages

### Phase 2: Campaign Collaboration
1. Invite code generation and joining system
2. Character selection for campaign joining
3. Campaign member management
4. Campaign detail pages with member display

### Phase 3: Rooms Foundation
1. Create domain structure for rooms
2. Database migrations and models
3. Room creation and basic management
4. Invite system for rooms

### Phase 4: Video Integration
1. Extend WebRTC system for variable layouts
2. Character selection for room joining
3. Dynamic slot management
4. Room-specific Ably channels

### Phase 5: Polish and Enhancement
1. UI/UX refinements
2. Error handling and validation
3. Performance optimization
4. Testing and bug fixes

## Security Considerations

### Access Control
- **Authentication**: All features require login
- **Campaign Privacy**: Invite-only access via unique codes
- **Room Security**: Password protection for rooms
- **Creator Permissions**: Only creators can manage their campaigns/rooms

### Data Validation
- **Input Sanitization**: Validate all user inputs
- **Rate Limiting**: Prevent spam creation of campaigns/rooms
- **Invite Code Security**: Ensure unique, non-guessable codes

## Performance Considerations

### Database Optimization
- **Indexing**: Proper indexes on lookup fields (invite codes, user IDs)
- **Query Optimization**: Efficient joins for member/participant data
- **Cleanup**: Archive old/inactive campaigns and rooms

### Video Performance
- **Connection Management**: Efficient peer connection handling
- **Resource Cleanup**: Proper cleanup when users leave
- **Scalability**: Consider connection limits for room sizes

## Testing Strategy

### Unit Testing
- Domain actions and repositories
- Data validation and transformation
- Business logic validation

### Integration Testing
- Campaign creation and joining flow
- Room creation and participation flow
- Character integration points

### Browser Testing (Laravel Dusk)
- Complete user workflows
- Video functionality testing
- Multi-user scenarios
- Cross-browser compatibility

## Success Metrics

### Campaign System
- Campaign creation rate
- Join success rate via invite links
- Active campaign retention
- User engagement with campaign features

### Room System
- Room creation and usage patterns
- Video connection success rates
- Session duration and quality
- User satisfaction with video features

## Future Enhancements

### Advanced Campaign Features
- Campaign scheduling and calendar integration
- Session logs and history
- Campaign-specific character sheets
- Integration with dice rolling systems

### Advanced Room Features
- Screen sharing capabilities
- Persistent room history
- Advanced moderation tools
- Integration with campaign system

### Integration Opportunities
- Link rooms to specific campaigns
- Campaign-based room permissions
- Character progression tracking across sessions
- Shared campaign resources and notes

---

*This document serves as the living specification for the Campaigns and Rooms features. It will be updated as requirements evolve and implementation details are refined.*
