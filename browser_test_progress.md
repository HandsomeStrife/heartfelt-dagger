# Browser Test Migration Progress

Converting from Laravel Dusk to Pest v4 browser testing. This document tracks the status of each test file.

## Legend
- ✅ **FIXED** - Test passes
- 🔄 **IN PROGRESS** - Currently being worked on
- ❌ **FAILED** - Test has issues that need manual review
- ⏸️ **SKIPPED** - Test deprecated/replaced with comments
- ⏳ **PENDING** - Not yet started

## Test Status Overview

### Basic/Setup Tests
- ✅ BasicPageLoadTest.php
- ✅ SimpleHomepageTest.php
- ✅ SimpleLivewireTest.php
- ⏸️ LivewireTest.php (DELETED - didn't test anything properly)
- ✅ ConsoleTest.php
- ✅ ConsoleWarningsTest.php
- ✅ PestBrowserSetupTest.php
- ✅ DockerPlaywrightSetupTest.php

### Character Builder Tests
- ✅ CharacterBuilderTest.php
- ✅ CharacterBuilderBasicTest.php
- ✅ CharacterBuilderNavigationTest.php
- ✅ CharacterBuilderSubHeaderTest.php
- ✅ CharacterBuilderSaveTimestampTest.php
- ✅ CharacterBuilderAncestryBonusTest.php
- ✅ CharacterAssociationTest.php
- ✅ CharacterDetailsSavingTest.php
- ✅ CharacterDeletionCsrfBrowserTest.php
- ✅ CharacterDeletionAuthenticatedTest.php
- ✅ CharacterDeletionAnonymousTest.php

### Audit Tests
- ✅ SpellcastTraitsAuditTest.php
- ✅ MechanicalBonusesAuditTest.php
- ✅ EquipmentValidationAuditTest.php
- ✅ DomainCardLimitsAuditTest.php
- ✅ PlaytestContentAuditTest.php
- ✅ NewClassesEnumTest.php

### Campaign Tests
- ✅ CampaignWorkflowTest.php
- ✅ CampaignBasicWorkflowTest.php
- ✅ CampaignFrameIntegrationTest.php
- ✅ CampaignFrameWorkflowTest.php
- ✅ CampaignFrameVisibilityBrowserTest.php
- ✅ CampaignFrameVisibilitySimpleTest.php
- ✅ CampaignFrameWorkingBrowserTest.php
- ✅ CampaignFrameBasicBrowserTest.php
- ✅ CampaignFrameFinalBrowserTest.php
- ✅ CampaignPagesWorkflowTest.php
- ✅ CampaignPagesVisibilityBrowserTest.php
- ✅ CampaignPagesCompleteWorkflowTest.php
- ✅ CampaignPagesHoverTest.php
- ✅ CampaignPagesImprovementsTest.php
- ✅ CampaignPagesSlideoverTest.php
- ✅ CampaignPagesSlideoverImprovementsTest.php
- ✅ CampaignPagesLayoutBugTest.php
- ✅ CampaignPagesCompactNavigationTest.php
- ✅ CampaignPagesDarkThemeTest.php

### Room/Session Tests
- ⏳ RoomWorkflowTest.php
- ⏳ RoomSessionTest.php
- ⏳ RoomSessionBasicTest.php
- ⏳ RoomSessionSimpleTest.php
- ⏳ AnonymousRoomJoinTest.php
- ⏳ RangeViewerTest.php

### Recording/Upload Tests
- ⏳ RoomRecordingIntegrationTest.php
- ⏳ RoomRecordingSettingsBrowserTest.php
- ⏳ WasabiEndToEndRecordingTest.php
- ⏳ WasabiAccountSetupBrowserTest.php
- ⏳ VideoLibraryBrowserTest.php
- ⏳ GoogleDriveDirectUploadBrowserTest.php
- ⏳ GoogleDriveOAuthBrowserTest.php
- ⏳ DirectUploadJavaScriptTest.php
- ⏳ S3MultipartRecordingBrowserTest.php
- ⏳ TranscriptViewingBrowserTest.php
- ⏳ SttConsentTest.php
- ⏳ WebRTCIceConfigIntegrationTest.php

### UI/Feature Tests
- ⏳ TiptapBoldFunctionalityTest.php
- ⏳ AuthPageRenderingTest.php

### Class Tests
- ⏳ ClassTests/AssassinClassTest.php
- ⏳ ClassTests/BardClassTest.php
- ⏳ ClassTests/BrawlerClassTest.php
- ⏳ ClassTests/DruidClassTest.php
- ⏳ ClassTests/GuardianClassTest.php
- ⏳ ClassTests/RangerClassTest.php
- ⏳ ClassTests/RogueClassTest.php
- ⏳ ClassTests/SeraphClassTest.php
- ⏳ ClassTests/SorcererClassTest.php
- ⏳ ClassTests/WarlockClassTest.php
- ⏳ ClassTests/WarriorClassTest.php
- ⏳ ClassTests/WitchClassTest.php
- ⏳ ClassTests/WizardClassTest.php

### Ancestry Tests
- ⏳ AncestryTests/EarthkinAncestryTest.php
- ⏳ AncestryTests/FaerieAncestryTest.php
- ⏳ AncestryTests/GalapaAncestryTest.php
- ⏳ AncestryTests/GiantAncestryTest.php
- ⏳ AncestryTests/HumanAncestryTest.php
- ⏳ AncestryTests/SimiahAncestryTest.php

### Bonus Tests
- ⏳ ClankAncestryBonusTest.php
- ⏳ SubclassDomainCardBonusTest.php

### Community Tests
- ⏳ CommunityTests/HighborneCommunityTest.php
- ⏳ CommunityTests/OrderborneCommunityTest.php
- ⏳ CommunityTests/SeaborneCommunityTest.php
- ⏳ CommunityTests/SlyborneCommunityTest.php
- ⏳ CommunityTests/WanderborneCommunityTest.php

## Common Issues Found
(Will be updated as tests are processed)

## Notes
- Started migration: [DATE]
- Total tests: 91
- Tests completed: 0
- Tests in progress: 0
- Tests failed: 0
- Tests skipped: 0
