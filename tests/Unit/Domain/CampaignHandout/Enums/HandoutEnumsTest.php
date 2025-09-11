<?php

declare(strict_types=1);

use Domain\CampaignHandout\Enums\HandoutAccessLevel;
use Domain\CampaignHandout\Enums\HandoutFileType;

describe('HandoutAccessLevel Enum', function () {
    
    describe('enum values', function () {
        it('has correct values', function () {
            expect(HandoutAccessLevel::GM_ONLY->value)->toBe('gm_only');
            expect(HandoutAccessLevel::ALL_PLAYERS->value)->toBe('all_players');
            expect(HandoutAccessLevel::SPECIFIC_PLAYERS->value)->toBe('specific_players');
        });

        it('can be created from string values', function () {
            expect(HandoutAccessLevel::from('gm_only'))->toBe(HandoutAccessLevel::GM_ONLY);
            expect(HandoutAccessLevel::from('all_players'))->toBe(HandoutAccessLevel::ALL_PLAYERS);
            expect(HandoutAccessLevel::from('specific_players'))->toBe(HandoutAccessLevel::SPECIFIC_PLAYERS);
        });

        it('returns all cases', function () {
            $cases = HandoutAccessLevel::cases();
            
            expect($cases)->toHaveCount(3);
            expect($cases)->toContain(HandoutAccessLevel::GM_ONLY);
            expect($cases)->toContain(HandoutAccessLevel::ALL_PLAYERS);
            expect($cases)->toContain(HandoutAccessLevel::SPECIFIC_PLAYERS);
        });
    });

    describe('utility methods', function () {
        it('gets display labels', function () {
            expect(HandoutAccessLevel::GM_ONLY->label())->toBe('GM Only');
            expect(HandoutAccessLevel::ALL_PLAYERS->label())->toBe('All Players');
            expect(HandoutAccessLevel::SPECIFIC_PLAYERS->label())->toBe('Specific Players');
        });

        it('gets descriptions', function () {
            expect(HandoutAccessLevel::GM_ONLY->description())->toBe('Only visible to the Game Master');
            expect(HandoutAccessLevel::ALL_PLAYERS->description())->toBe('Visible to all campaign players');
            expect(HandoutAccessLevel::SPECIFIC_PLAYERS->description())->toBe('Visible to selected players only');
        });

        it('determines if can be viewed by all', function () {
            expect(HandoutAccessLevel::GM_ONLY->canBeViewedByAll())->toBeFalse();
            expect(HandoutAccessLevel::ALL_PLAYERS->canBeViewedByAll())->toBeTrue();
            expect(HandoutAccessLevel::SPECIFIC_PLAYERS->canBeViewedByAll())->toBeFalse();
        });

        it('determines if requires specific access', function () {
            expect(HandoutAccessLevel::GM_ONLY->requiresSpecificAccess())->toBeFalse();
            expect(HandoutAccessLevel::ALL_PLAYERS->requiresSpecificAccess())->toBeFalse();
            expect(HandoutAccessLevel::SPECIFIC_PLAYERS->requiresSpecificAccess())->toBeTrue();
        });
    });
});

describe('HandoutFileType Enum', function () {
    
    describe('enum values', function () {
        it('has correct values', function () {
            expect(HandoutFileType::IMAGE->value)->toBe('image');
            expect(HandoutFileType::PDF->value)->toBe('pdf');
            expect(HandoutFileType::DOCUMENT->value)->toBe('document');
            expect(HandoutFileType::AUDIO->value)->toBe('audio');
            expect(HandoutFileType::VIDEO->value)->toBe('video');
            expect(HandoutFileType::OTHER->value)->toBe('other');
        });

        it('can be created from string values', function () {
            expect(HandoutFileType::from('image'))->toBe(HandoutFileType::IMAGE);
            expect(HandoutFileType::from('pdf'))->toBe(HandoutFileType::PDF);
            expect(HandoutFileType::from('document'))->toBe(HandoutFileType::DOCUMENT);
            expect(HandoutFileType::from('audio'))->toBe(HandoutFileType::AUDIO);
            expect(HandoutFileType::from('video'))->toBe(HandoutFileType::VIDEO);
            expect(HandoutFileType::from('other'))->toBe(HandoutFileType::OTHER);
        });

        it('returns all cases', function () {
            $cases = HandoutFileType::cases();
            
            expect($cases)->toHaveCount(6);
            expect($cases)->toContain(HandoutFileType::IMAGE);
            expect($cases)->toContain(HandoutFileType::PDF);
            expect($cases)->toContain(HandoutFileType::DOCUMENT);
            expect($cases)->toContain(HandoutFileType::AUDIO);
            expect($cases)->toContain(HandoutFileType::VIDEO);
            expect($cases)->toContain(HandoutFileType::OTHER);
        });
    });

    describe('utility methods', function () {
        it('gets icon SVG paths', function () {
            expect(HandoutFileType::IMAGE->icon())->toBeString();
            expect(HandoutFileType::PDF->icon())->toBeString();
            expect(HandoutFileType::DOCUMENT->icon())->toBeString();
            expect(HandoutFileType::AUDIO->icon())->toBeString();
            expect(HandoutFileType::VIDEO->icon())->toBeString();
            expect(HandoutFileType::OTHER->icon())->toBeString();
        });

        it('determines if previewable', function () {
            expect(HandoutFileType::IMAGE->isPreviewable())->toBeTrue();
            expect(HandoutFileType::PDF->isPreviewable())->toBeTrue();
            expect(HandoutFileType::DOCUMENT->isPreviewable())->toBeFalse();
            expect(HandoutFileType::AUDIO->isPreviewable())->toBeFalse();
            expect(HandoutFileType::VIDEO->isPreviewable())->toBeFalse();
            expect(HandoutFileType::OTHER->isPreviewable())->toBeFalse();
        });

        it('determines if downloadable', function () {
            expect(HandoutFileType::IMAGE->isDownloadable())->toBeTrue();
            expect(HandoutFileType::PDF->isDownloadable())->toBeTrue();
            expect(HandoutFileType::DOCUMENT->isDownloadable())->toBeTrue();
            expect(HandoutFileType::AUDIO->isDownloadable())->toBeTrue();
            expect(HandoutFileType::VIDEO->isDownloadable())->toBeTrue();
            expect(HandoutFileType::OTHER->isDownloadable())->toBeTrue();
        });

        it('determines file type from mime type', function () {
            expect(HandoutFileType::fromMimeType('image/jpeg'))->toBe(HandoutFileType::IMAGE);
            expect(HandoutFileType::fromMimeType('application/pdf'))->toBe(HandoutFileType::PDF);
            expect(HandoutFileType::fromMimeType('text/plain'))->toBe(HandoutFileType::DOCUMENT);
            expect(HandoutFileType::fromMimeType('audio/mpeg'))->toBe(HandoutFileType::AUDIO);
            expect(HandoutFileType::fromMimeType('video/mp4'))->toBe(HandoutFileType::VIDEO);
            expect(HandoutFileType::fromMimeType('application/octet-stream'))->toBe(HandoutFileType::OTHER);
        });

        it('gets acceptable extensions', function () {
            $imageExts = HandoutFileType::IMAGE->acceptableExtensions();
            expect($imageExts)->toContain('jpg');
            expect($imageExts)->toContain('jpeg');
            expect($imageExts)->toContain('png');
            expect($imageExts)->toContain('gif');
            expect($imageExts)->toContain('webp');

            $pdfExts = HandoutFileType::PDF->acceptableExtensions();
            expect($pdfExts)->toContain('pdf');

            $docExts = HandoutFileType::DOCUMENT->acceptableExtensions();
            expect($docExts)->toContain('doc');
            expect($docExts)->toContain('docx');
            expect($docExts)->toContain('txt');

            $audioExts = HandoutFileType::AUDIO->acceptableExtensions();
            expect($audioExts)->toContain('mp3');
            expect($audioExts)->toContain('wav');

            $videoExts = HandoutFileType::VIDEO->acceptableExtensions();
            expect($videoExts)->toContain('mp4');
            expect($videoExts)->toContain('webm');

            $otherExts = HandoutFileType::OTHER->acceptableExtensions();
            expect($otherExts)->toContain('*');
        });
    });
});
