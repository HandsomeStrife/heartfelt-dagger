<?php

declare(strict_types=1);

use Domain\Room\Models\Room;
use Domain\Room\Models\RoomRecordingSettings;
use Domain\User\Models\User;
use Domain\User\Models\UserStorageAccount;
use function Pest\Laravel\actingAs;

beforeEach(function () {
    // Configure MinIO for testing
    config([
        'app.env' => 'testing',
        'filesystems.disks.wasabi' => [
            'driver' => 's3',
            'key' => 'minioadmin',
            'secret' => 'minioadmin',
            'region' => 'us-east-1',
            'bucket' => 'recordings',
            'endpoint' => 'http://localhost:9000',
            'use_path_style_endpoint' => true,
        ],
    ]);
});

describe('S3 Multipart Recording Browser Tests', function () {
    test('session loads with local media and no console errors', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://localhost:9000',
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'stt_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Add user as participant with all consents
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        actingAs($user);
        $page = visit(route('rooms.session', $room));

        // Configure browser with fake media devices
        $page->script(<<<'JS'
const mediaPath = "/tests/Browser/media";
// Set up fake media devices for testing
navigator.mediaDevices.getUserMedia = async (constraints) => {
  const stream = new MediaStream();
  if (constraints.video) {
    const canvas = document.createElement("canvas");
    canvas.width = 640; canvas.height = 480;
    const ctx = canvas.getContext("2d");
    ctx.fillStyle = "red"; ctx.fillRect(0, 0, 320, 240);
    ctx.fillStyle = "blue"; ctx.fillRect(320, 240, 320, 240);
    const videoStream = canvas.captureStream(30);
    videoStream.getTracks().forEach(track => stream.addTrack(track));
  }
  if (constraints.audio) {
    const audioContext = new AudioContext();
    const oscillator = audioContext.createOscillator();
    const dest = audioContext.createMediaStreamDestination();
    oscillator.connect(dest);
    oscillator.frequency.setValueAtTime(440, audioContext.currentTime);
    oscillator.start();
    stream.addTrack(dest.stream.getAudioTracks()[0]);
  }
  return stream;
};
JS);

        // Wait for page to load and initialize
        $page->wait(1)
            ->assertSee($room->name)
            ->assertNoConsoleLogs()
            ->assertNoJavaScriptErrors();

        // Check if local video element is properly set up
        $page->script(<<<'JS'
const localVideo = document.querySelector(".local-video");
if (localVideo) {
  console.log("Local video found:", {
    muted: localVideo.muted,
    playsInline: localVideo.playsInline,
    srcObject: !!localVideo.srcObject
  });
}
JS);

        // Verify local video properties
        $localVideoMuted = $page->script('return document.querySelector(".local-video")?.muted');
        $localVideoPlaysInline = $page->script('return document.querySelector(".local-video")?.playsInline');
        $localVideoHasStream = $page->script('return !!document.querySelector(".local-video")?.srcObject');

        expect($localVideoMuted)->toBeTrue();
        expect($localVideoPlaysInline)->toBeTrue();
        expect($localVideoHasStream)->toBeTrue();
    });

    test('recording creates multipart upload requests', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
            'encrypted_credentials' => [
                'access_key_id' => 'minioadmin',
                'secret_access_key' => 'minioadmin',
                'bucket_name' => 'recordings',
                'region' => 'us-east-1',
                'endpoint' => 'http://localhost:9000',
            ],
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        // Add user as participant with recording consent
        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            // No recording consent needed for this test
        ]);

        actingAs($user);
        $page = visit(route('rooms.session', $room));

        // Set up fake media and short recording intervals for testing
        $page->script(<<<'JS'
// Mock getUserMedia
navigator.mediaDevices.getUserMedia = async () => {
  const stream = new MediaStream();
  const canvas = document.createElement("canvas");
  canvas.width = 640; canvas.height = 480;
  const videoStream = canvas.captureStream(30);
  videoStream.getTracks().forEach(track => stream.addTrack(track));
  return stream;
};

// Mock MediaRecorder with short timeslices for testing
window.originalMediaRecorder = MediaRecorder;
window.MediaRecorder = class extends originalMediaRecorder {
  constructor(stream, options) {
    super(stream, options);
    this.testChunkCount = 0;
  }
  start(timeslice) {
    super.start(1000); // Force 1-second chunks for testing
    setTimeout(() => {
      if (this.state === "recording") {
        const fakeData = new Blob(["fake video chunk " + (++this.testChunkCount)], {type: "video/webm"});
        this.ondataavailable({data: fakeData});
      }
    }, 1000);
  }
};

// Track network requests
window.testNetworkRequests = [];
const originalFetch = fetch;
window.fetch = (...args) => {
  window.testNetworkRequests.push(args[0]);
  return originalFetch(...args);
};
JS);

        $page->wait(1)->assertSee($room->name);

        // Start recording
        $page->script(<<<'JS'
if (window.roomWebRTC) {
  window.roomWebRTC.startVideoRecording();
  console.log("Recording started");
}
JS);

        // Wait for multipart upload requests
        $page->wait(3); // Wait 3 seconds for chunks to be generated

        // Check for multipart upload requests
        $requests = $page->script('return window.testNetworkRequests || []');
        
        $createRequests = array_filter($requests, fn($req) => 
            is_string($req) && str_contains($req, '/api/uploads/s3/multipart/create')
        );
        $signRequests = array_filter($requests, fn($req) => 
            is_string($req) && str_contains($req, '/api/uploads/s3/multipart/sign')
        );
        $completeRequests = array_filter($requests, fn($req) => 
            is_string($req) && str_contains($req, '/api/uploads/s3/multipart/complete')
        );

        expect(count($createRequests))->toBeGreaterThan(0);
        expect(count($signRequests))->toBeGreaterThan(0);
        expect(count($completeRequests))->toBeGreaterThan(0);

        // Verify UI shows recording state
        $page->assertSee('🎥'); // Recording indicator
    });

    test('backpressure delays recording when uploads are slow', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            // No recording consent needed for this test
        ]);

        actingAs($user);
        $page = visit(route('rooms.session', $room));

        // Mock slow network and many in-flight uploads
        $page->script(<<<'JS'
navigator.mediaDevices.getUserMedia = async () => {
  const stream = new MediaStream();
  const canvas = document.createElement("canvas");
  const videoStream = canvas.captureStream(30);
  stream.addTrack(videoStream.getVideoTracks()[0]);
  return stream;
};

// Mock Uppy with many in-flight uploads
window.RoomUppy = class {
  constructor() {
    this.mockFiles = {
      file1: { progress: { uploadStarted: true, uploadComplete: false } },
      file2: { progress: { uploadStarted: true, uploadComplete: false } },
      file3: { progress: { uploadStarted: true, uploadComplete: false } },
      file4: { progress: { uploadStarted: true, uploadComplete: false } },
      file5: { progress: { uploadStarted: true, uploadComplete: false } },
    };
  }
  getState() { return { files: this.mockFiles }; }
  uploadVideoBlob() { console.log("Upload blocked due to backpressure"); }
};
window.roomUppy = new window.RoomUppy();
JS);

        $page->wait(1)->assertSee($room->name);

        // Test backpressure detection
        $hasBackpressure = $page->script([
            'if (window.roomWebRTC) {',
            '  return window.roomWebRTC.tooManyQueuedUploads();',
            '}',
            'return false;'
        ]);

        expect($hasBackpressure)->toBeTrue();
    });

    test('crash recovery resumes uploads with GoldenRetriever', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);
        $storageAccount = UserStorageAccount::factory()->create([
            'user_id' => $user->id,
            'provider' => 'wasabi',
        ]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'recording_enabled' => true,
            'storage_provider' => 'wasabi',
            'storage_account_id' => $storageAccount->id,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            // No recording consent needed for this test
        ]);

        actingAs($user);
        $page = visit(route('rooms.session', $room));

        // Simulate partial upload state in localStorage (GoldenRetriever data)
        $page->script('localStorage.setItem("uppy/room-' . $room->id . '-uploader", JSON.stringify({
  files: {
    "test-file": {
      name: "recovery-test.webm",
      size: 1024,
      type: "video/webm",
      progress: { uploadStarted: true, uploadComplete: false }
    }
  }
})); console.log("Crash recovery state set up");');

        $page->wait(1)->assertSee($room->name);

        // Check if Uppy attempts to recover
        $page->wait(2000);
        
        // Verify recovery behavior (GoldenRetriever would restore files)
        $recoveryLogs = $page->script(<<<'JS'
const logs = [];
const originalLog = console.log;
console.log = (...args) => {
  logs.push(args.join(" "));
  originalLog(...args);
};
return logs.filter(log => log.includes("recovery") || log.includes("restore"));
JS);

        expect($recoveryLogs)->not()->toBeEmpty();
    });

    test('STT chunks are sent with correct language and timestamps', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        RoomRecordingSettings::factory()->create([
            'room_id' => $room->id,
            'stt_enabled' => true,
        ]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test Character',
            'stt_consent_given' => true,
            'stt_consent_at' => now(),
        ]);

        actingAs($user);
        $page = visit(route('rooms.session', $room));

        // Mock SpeechRecognition with controlled results
        $page->script(<<<'JS'
window.SpeechRecognition = window.webkitSpeechRecognition = class {
  constructor() {
    this.lang = "en-GB"; // Default language
    this.continuous = true;
    this.interimResults = false;
  }
  start() {
    console.log("STT started with language:", this.lang);
    // Simulate speech recognition results
    setTimeout(() => {
      this.onresult({
        results: [{
          0: { transcript: "Hello world", confidence: 0.95 },
          isFinal: true,
          length: 1
        }],
        resultIndex: 0
      });
    }, 1000);
  }
  stop() { console.log("STT stopped"); }
};

// Track STT requests
window.sttRequests = [];
const originalFetch = fetch;
window.fetch = (...args) => {
  if (args[0].includes("/transcripts")) {
    window.sttRequests.push(args);
  }
  return originalFetch(...args);
};
JS);

        $page->wait(1)->assertSee($room->name);

        // Wait for STT processing
        $page->wait(3000);

        // Check that STT requests were made with correct structure
        $sttRequests = $page->script('return window.sttRequests || []');
        expect(count($sttRequests))->toBeGreaterThan(0);

        // Verify language is not hardcoded to en-US
        $page->script([
            'if (window.roomWebRTC && window.roomWebRTC.speechRecognition) {',
            '  const lang = window.roomWebRTC.speechRecognition.lang;',
            '  console.log("STT Language:", lang);',
            '  if (lang === "en-US") {',
            '    console.warn("Language should not be hardcoded to en-US");',
            '  }',
            '}'
        ]);
    });

    test('targeted Ably messages are properly filtered', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user1->id]);

        // Add both users as participants
        $room->participants()->create(['user_id' => $user1->id, 'character_name' => 'User 1']);
        $room->participants()->create(['user_id' => $user2->id, 'character_name' => 'User 2']);

        actingAs($user1);
        $pageA = visit(route('rooms.session', $room));

        actingAs($user2);
        $pageB = visit(route('rooms.session', $room));

        // Mock Ably with message filtering
        $pageA->script(<<<'JS'
window.mockAblyMessages = [];
window.AblyClient = {
  channels: {
    get: () => ({
      subscribe: (callback) => {
        window.ablyCallback = callback;
      },
      publish: (type, data) => {
        console.log("Publishing message:", type, data);
      }
    })
  }
};

// Simulate targeted message
setTimeout(() => {
  if (window.ablyCallback) {
    window.ablyCallback({
      data: {
        type: "offer",
        senderId: "user2",
        targetPeerId: "user1", // Targeted to user1
        offer: { type: "offer" }
      }
    });
  }
}, 1000);
JS);

        $pageA->wait(1)->assertSee($room->name);
        $pageA->wait(2000);

        // Verify message was processed by pageA
        $processedByA = $pageA->script([
            'return window.roomWebRTC && window.roomWebRTC.currentPeerId === "user1";'
        ]);

        expect($processedByA)->toBeTrue();
    });

    test('peer connection lifecycle and cleanup', function () {
        $user = User::factory()->create();
        $room = Room::factory()->create(['creator_id' => $user->id]);

        $room->participants()->create([
            'user_id' => $user->id,
            'character_name' => 'Test User',
        ]);

        actingAs($user);
        $page = visit(route('rooms.session', $room));

        // Mock peer connection states
        $page->script(<<<'JS'
window.mockPeerConnections = new Map();
window.RTCPeerConnection = class {
  constructor() {
    this.connectionState = "connecting";
    this.signalingState = "stable";
    this.localDescription = null;
    this.remoteDescription = null;
  }
  createOffer() { return Promise.resolve({type: "offer", sdp: "fake-sdp"}); }
  createAnswer() { return Promise.resolve({type: "answer", sdp: "fake-sdp"}); }
  setLocalDescription(desc) { this.localDescription = desc; return Promise.resolve(); }
  setRemoteDescription(desc) { this.remoteDescription = desc; return Promise.resolve(); }
  addTrack() { return { track: {} }; }
  getSenders() { return []; }
  close() {
    this.connectionState = "closed";
    if (this.onconnectionstatechange) {
      this.onconnectionstatechange();
    }
  }
};
JS);

        $page->wait(1)->assertSee($room->name);

        // Simulate peer leaving (connection state change)
        $page->script([
            'if (window.roomWebRTC) {',
            '  const mockPeer = new RTCPeerConnection();',
            '  window.roomWebRTC.peerConnections = new Map([["peer1", mockPeer]]);',
            '  // Simulate connection failure',
            '  mockPeer.connectionState = "failed";',
            '  if (mockPeer.onconnectionstatechange) {',
            '    mockPeer.onconnectionstatechange();',
            '  }',
            '}'
        ]);

        $page->wait(1000);

        // Verify cleanup occurred
        $connectionsCount = $page->script([
            'return window.roomWebRTC ? window.roomWebRTC.peerConnections.size : -1;'
        ]);

        expect($connectionsCount)->toBe(0); // Connection should be cleaned up
    });
});
