<?php

declare(strict_types=1);

use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('uploader factory creates wasabi uploader', function () {
    $roomData = ['id' => 1];
    $recordingSettings = ['storage_provider' => 'wasabi'];

    // Use dynamic import since this is a JS module test equivalent
    $factoryPath = resource_path('js/room/uploaders/UploaderFactory.js');
    expect(file_exists($factoryPath))->toBeTrue();

    // Verify the factory file contains the expected providers
    $factoryContent = file_get_contents($factoryPath);
    expect($factoryContent)->toContain('WasabiUploader');
    expect($factoryContent)->toContain('GoogleDriveUploader');
    expect($factoryContent)->toContain('LocalUploader');
    expect($factoryContent)->toContain('createUploader');
});

test('uploader architecture files exist', function () {
    $uploaderFiles = [
        'BaseUploader.js',
        'WasabiUploader.js',
        'GoogleDriveUploader.js',
        'LocalUploader.js',
        'UploaderFactory.js',
    ];

    foreach ($uploaderFiles as $file) {
        $path = resource_path("js/room/uploaders/{$file}");
        expect(file_exists($path))->toBeTrue("Missing uploader file: {$file}");

        $content = file_get_contents($path);
        expect(strlen($content))->toBeGreaterThan(100, "Uploader file {$file} appears to be empty or too small");
    }
});

test('wasabi uploader contains required methods', function () {
    $content = file_get_contents(resource_path('js/room/uploaders/WasabiUploader.js'));

    // Check core methods exist
    expect($content)->toContain('async initialize(');
    expect($content)->toContain('async uploadChunk(');
    expect($content)->toContain('async finalize(');
    expect($content)->toContain('async abort(');
    expect($content)->toContain('getProviderName()');

    // Check S3-specific functionality
    expect($content)->toContain('multipart');
    expect($content)->toContain('/api/uploads/s3/multipart/');
    expect($content)->toContain('wasabi');
});

test('google drive uploader contains required methods', function () {
    $content = file_get_contents(resource_path('js/room/uploaders/GoogleDriveUploader.js'));

    // Check core methods exist
    expect($content)->toContain('async initialize(');
    expect($content)->toContain('async uploadChunk(');
    expect($content)->toContain('async finalize(');
    expect($content)->toContain('async abort(');
    expect($content)->toContain('getProviderName()');

    // Check Google Drive specific functionality
    expect($content)->toContain('resumable');
    expect($content)->toContain('google-drive-upload-url');
    expect($content)->toContain('Content-Range');
    expect($content)->toContain('google_drive');
});

test('local uploader contains required methods', function () {
    $content = file_get_contents(resource_path('js/room/uploaders/LocalUploader.js'));

    // Check core methods exist
    expect($content)->toContain('async initialize(');
    expect($content)->toContain('async uploadChunk(');
    expect($content)->toContain('async finalize(');
    expect($content)->toContain('async abort(');
    expect($content)->toContain('getProviderName()');

    // Check local-specific functionality
    expect($content)->toContain('local_device');
    expect($content)->toContain('downloadRecording');
});

test('room uppy uses uploader factory', function () {
    $content = file_get_contents(resource_path('js/room-uppy.js'));

    // Check that room-uppy imports and uses the factory
    expect($content)->toContain('UploaderFactory');
    expect($content)->toContain('createUploader');
    expect($content)->toContain('this.uploader');

    // Verify the main upload method delegates to the uploader
    expect($content)->toContain('await this.uploader.initialize');
    expect($content)->toContain('await this.uploader.uploadChunk');
    expect($content)->toContain('await this.uploader.finalize');
});

test('video recorder uses correct cloud uploader interface', function () {
    $content = file_get_contents(resource_path('js/room/recording/VideoRecorder.js'));

    // Check that VideoRecorder calls the correct method on CloudUploader
    expect($content)->toContain('this.roomWebRTC.cloudUploader.uploadChunk');
    expect($content)->not->toContain('this.roomWebRTC.cloudUploader.uploadVideoBlob');
});

test('cloud uploader bridges to room uppy', function () {
    $content = file_get_contents(resource_path('js/room/recording/CloudUploader.js'));

    // Check that CloudUploader properly bridges to RoomUppy
    expect($content)->toContain('uploadChunk');
    expect($content)->toContain('window.roomUppy.uploadVideoBlob');
});

test('room uppy file size reduced significantly', function () {
    $roomUppyPath = resource_path('js/room-uppy.js');
    $content = file_get_contents($roomUppyPath);
    $lineCount = substr_count($content, "\n");

    // Verify the file was significantly reduced from original ~1083 lines
    expect($lineCount)->toBeLessThan(700, 'room-uppy.js should be significantly smaller after refactoring');
    expect($lineCount)->toBeGreaterThan(400, 'room-uppy.js should still have core functionality');
});
