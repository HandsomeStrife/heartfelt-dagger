# Campaign System Testing Outline

## Overview

This document outlines comprehensive testing requirements for the DaggerHeart Campaign System. Tests are organized by type and scope to ensure complete coverage of functionality, security, and user experience.

## Test Categories

### 1. Unit Tests

#### Domain Layer Tests

**Campaign Model Tests (`tests/Unit/Domain/Campaign/Models/CampaignTest.php`)**
- ✅ Test invite code generation uniqueness
- ✅ Test campaign code generation uniqueness
- ✅ Test scope methods (byInviteCode, byCampaignCode, byCreator, active)
- ✅ Test relationships (creator, members)
- ✅ Test member checking methods (isCreator, hasMember)
- ✅ Test member count calculation
- ✅ Test route key name returns campaign_code
- ✅ Test status enum casting

**CampaignMember Model Tests (`tests/Unit/Domain/Campaign/Models/CampaignMemberTest.php`)**
- ✅ Test relationships (campaign, user, character)
- ✅ Test hasCharacter method
- ✅ Test display methods (getDisplayName, getCharacterClass, etc.)
- ✅ Test scopes (withCharacters, withoutCharacters)

**Campaign Actions Tests**

`tests/Unit/Domain/Campaign/Actions/CreateCampaignActionTest.php`
- ✅ Test successful campaign creation
- ✅ Test auto-generation of codes
- ✅ Test creator relationship setup
- ✅ Test validation of input data

`tests/Unit/Domain/Campaign/Actions/JoinCampaignActionTest.php`
- ✅ Test successful join with character
- ✅ Test successful join without character (empty slot)
- ✅ Test prevention of duplicate membership
- ✅ Test character ownership validation
- ✅ Test exception handling for invalid scenarios

`tests/Unit/Domain/Campaign/Actions/LeaveCampaignActionTest.php`
- ✅ Test successful leaving
- ✅ Test prevention of creator leaving own campaign
- ✅ Test exception handling for non-members
- ✅ Test membership cleanup

**Campaign Repository Tests (`tests/Unit/Domain/Campaign/Repositories/CampaignRepositoryTest.php`)**
- ✅ Test findById with member count
- ✅ Test findByInviteCode with relationships
- ✅ Test getCreatedByUser with ordering
- ✅ Test getJoinedByUser with relationships
- ✅ Test getCampaignMembers with character data
- ✅ Test getActiveCampaigns filtering
- ✅ Test getAllUserCampaigns combining created and joined

**Campaign Data Objects Tests**

`tests/Unit/Domain/Campaign/Data/CampaignDataTest.php`
- ✅ Test data object creation from array
- ✅ Test data object creation from model
- ✅ Test Wireable functionality for Livewire
- ✅ Test validation rules

`tests/Unit/Domain/Campaign/Data/CreateCampaignDataTest.php`
- ✅ Test validation rules (name max 100, description max 1000)
- ✅ Test required field validation
- ✅ Test data transformation

### 2. Feature Tests (HTTP Layer)

**Campaign Controller Tests (`tests/Feature/Http/Controllers/CampaignControllerTest.php`)**

**Authentication Tests**
- ✅ Test all routes require authentication
- ✅ Test guest users redirected to login

**Campaign Index Tests**
- ✅ Test campaign dashboard loads correctly
- ✅ Test displays created campaigns
- ✅ Test displays joined campaigns
- ✅ Test empty states render correctly
- ✅ Test campaign cards show correct information

**Campaign Creation Tests**
- ✅ Test create form loads correctly
- ✅ Test successful campaign creation
- ✅ Test validation errors displayed
- ✅ Test redirect to campaign show page
- ✅ Test campaign codes generated correctly

**Campaign Show Tests**
- ✅ Test campaign detail page loads
- ✅ Test member list displays correctly
- ✅ Test creator sees management options
- ✅ Test members see leave option
- ✅ Test non-members see appropriate content
- ✅ Test invite link copying functionality

**Campaign Join Flow Tests**
- ✅ Test join form via invite code
- ✅ Test character selection displayed
- ✅ Test successful join with character
- ✅ Test successful join without character
- ✅ Test duplicate join prevention
- ✅ Test invalid invite code handling
- ✅ Test character ownership validation

**Campaign Leave Tests**
- ✅ Test successful member leaving
- ✅ Test creator cannot leave prevention
- ✅ Test non-member leave attempt prevention

**Route Parameter Tests**
- ✅ Test campaign_code routing works correctly
- ✅ Test invite_code routing for join links
- ✅ Test 404 handling for invalid codes

### 3. Integration Tests

**Database Integration Tests (`tests/Integration/Campaign/`)**

**Campaign Creation Integration**
- ✅ Test complete campaign creation flow
- ✅ Test database relationships are established
- ✅ Test code uniqueness across large datasets
- ✅ Test transaction rollback on errors

**Campaign Membership Integration**
- ✅ Test complete join flow with character selection
- ✅ Test membership queries with eager loading
- ✅ Test member removal and cascade effects
- ✅ Test character relationship handling

**Campaign Repository Integration**
- ✅ Test complex queries with multiple relationships
- ✅ Test pagination of large campaign lists
- ✅ Test performance of member count calculations
- ✅ Test data consistency across operations

### 4. Browser Tests (Laravel Dusk)

**Campaign Workflow Tests (`tests/Browser/Campaign/`)**

**Complete User Journey Tests (`tests/Browser/Campaign/CampaignWorkflowTest.php`)**
- ✅ Test complete campaign creation to joining workflow
- ✅ Test user creates campaign → shares invite → another user joins
- ✅ Test character selection during join process
- ✅ Test campaign member management from creator perspective

**Multi-User Scenarios (`tests/Browser/Campaign/MultiUserCampaignTest.php`)**
- ✅ Test multiple users joining same campaign
- ✅ Test concurrent character selection
- ✅ Test member list updates in real-time (if applicable)
- ✅ Test invite link sharing between users

**Campaign Management Tests (`tests/Browser/Campaign/CampaignManagementTest.php`)**
- ✅ Test campaign dashboard navigation
- ✅ Test filtering between created and joined campaigns
- ✅ Test campaign detail view interactions
- ✅ Test invite link copying and usage

**Character Integration Tests (`tests/Browser/Campaign/CharacterIntegrationTest.php`)**
- ✅ Test joining with existing characters
- ✅ Test joining with empty character slot
- ✅ Test character display in member lists
- ✅ Test character creation flow integration

**Mobile/Responsive Tests (`tests/Browser/Campaign/ResponsiveTest.php`)**
- ✅ Test campaign creation on mobile devices
- ✅ Test campaign browsing on tablets
- ✅ Test invite sharing on mobile
- ✅ Test responsive card layouts

### 5. Security Tests

**Access Control Tests (`tests/Feature/Security/CampaignSecurityTest.php`)**
- ✅ Test users can only see campaigns they created or joined
- ✅ Test invite code security (no enumeration)
- ✅ Test campaign code security (no enumeration)
- ✅ Test character ownership validation on join
- ✅ Test creator permissions enforcement

**Input Validation Tests**
- ✅ Test SQL injection prevention in campaign names/descriptions
- ✅ Test XSS prevention in campaign content
- ✅ Test CSRF protection on all forms
- ✅ Test file upload restrictions (if applicable)

**Rate Limiting Tests**
- ✅ Test campaign creation rate limiting
- ✅ Test invite code generation rate limiting
- ✅ Test join attempt rate limiting

### 6. Performance Tests

**Database Performance Tests (`tests/Performance/Campaign/`)**
- ✅ Test campaign queries with large datasets (1000+ campaigns)
- ✅ Test member queries with many members (100+ per campaign)
- ✅ Test code generation performance under load
- ✅ Test eager loading optimization effectiveness

**Page Load Performance Tests**
- ✅ Test campaign dashboard load time with many campaigns
- ✅ Test campaign detail page with many members
- ✅ Test character selection page performance

### 7. Error Handling Tests

**Exception Handling Tests (`tests/Feature/Campaign/ErrorHandlingTest.php`)**
- ✅ Test database connection failures
- ✅ Test invalid campaign code handling
- ✅ Test expired invite code scenarios
- ✅ Test deleted character handling in campaigns
- ✅ Test user deletion cascade effects

**Validation Error Tests**
- ✅ Test campaign name too long (>100 chars)
- ✅ Test campaign description too long (>1000 chars)
- ✅ Test empty required fields
- ✅ Test invalid character selection

## Test Data Requirements

### Factories

**Campaign Factory (`database/factories/CampaignFactory.php`)**
```php
return [
    'name' => $this->faker->words(3, true),
    'description' => $this->faker->paragraph,
    'creator_id' => User::factory(),
    'invite_code' => Campaign::generateUniqueInviteCode(),
    'campaign_code' => Campaign::generateUniqueCampaignCode(),
    'status' => CampaignStatus::ACTIVE,
];
```

**CampaignMember Factory (`database/factories/CampaignMemberFactory.php`)**
```php
return [
    'campaign_id' => Campaign::factory(),
    'user_id' => User::factory(),
    'character_id' => Character::factory(),
    'joined_at' => now(),
];
```

### Seeders

**Campaign Test Seeder**
- Create campaigns with various member counts
- Create campaigns in different statuses
- Create campaigns with mixed character/empty memberships

## Test Execution Strategy

### 1. Development Testing
- Run unit tests continuously during development
- Use `php artisan test --group=campaign` for focused testing
- Implement test-driven development for new features

### 2. Pre-Commit Testing
- Run full campaign test suite before commits
- Ensure browser tests pass in headless mode
- Validate performance benchmarks

### 3. CI/CD Pipeline Testing
- Run complete test suite on pull requests
- Include browser testing in multiple browsers
- Performance regression testing
- Security vulnerability scanning

### 4. Production Testing
- Smoke tests for critical campaign functionality
- Monitoring of campaign creation/join rates
- Database query performance monitoring

## Test Environment Setup

### Database Configuration
```php
// config/database.php (testing)
'testing' => [
    'driver' => 'sqlite',
    'database' => ':memory:',
    'foreign_key_constraints' => true,
],
```

### Browser Testing Setup
```php
// .env.dusk.local
APP_URL=http://localhost:8000
DB_CONNECTION=testing
DUSK_DRIVER_URL=http://selenium:4444/wd/hub
```

### Performance Testing Setup
- Use dedicated test database with realistic data volumes
- Configure profiling tools for query analysis
- Set performance thresholds for test failures

## Test Coverage Goals

- **Unit Tests**: 95% coverage of domain layer
- **Feature Tests**: 90% coverage of HTTP layer
- **Browser Tests**: 100% coverage of critical user workflows
- **Security Tests**: 100% coverage of access control
- **Performance Tests**: All critical queries under defined thresholds

## Continuous Monitoring

### Metrics to Track
- Test execution time trends
- Test failure rates by category
- Code coverage percentages
- Performance benchmark compliance
- Security test results

### Alerting
- Immediate alerts for security test failures
- Performance degradation alerts
- Critical workflow test failures
- Coverage percentage drops

This comprehensive testing outline ensures the campaign system is robust, secure, and performant across all user scenarios and edge cases.
