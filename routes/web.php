<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\LogoutController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\VideoRoomController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect('/dashboard');
    }

    return view('index');
})->name('home');

// Authentication routes (guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');

    Route::get('/register', [RegisterController::class, 'index'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->name('register.post');
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout');

// Discord redirect
Route::get('/discord', function () {
    return redirect('https://discord.gg/dNAkDYevGx');
})->name('discord');

// Legal pages
Route::get('/terms-of-service', function () {
    return view('legal.terms-of-service');
})->name('terms-of-service');

Route::get('/privacy-policy', function () {
    return view('legal.privacy-policy');
})->name('privacy-policy');

// Protected routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
    
    // TipTap editor test route
    Route::get('/tiptap-test', App\Livewire\TiptapTest::class)->name('tiptap-test');
    
    // Campaign routes
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::get('/', [App\Http\Controllers\CampaignController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\CampaignController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CampaignController::class, 'store'])->name('store');
        Route::post('/join', [App\Http\Controllers\CampaignController::class, 'joinByCode'])->name('join');
        Route::get('/{campaign}', [App\Http\Controllers\CampaignController::class, 'show'])->name('show');
        Route::post('/{campaign}/join', [App\Http\Controllers\CampaignController::class, 'join'])->name('join_campaign');
        Route::delete('/{campaign}/leave', [App\Http\Controllers\CampaignController::class, 'leave'])->name('leave');
        Route::get('/{campaign}/rooms/create', [App\Http\Controllers\RoomController::class, 'createForCampaign'])->name('rooms.create');
        Route::post('/{campaign}/rooms', [App\Http\Controllers\RoomController::class, 'storeForCampaign'])->name('rooms.store');
        
        // Campaign Pages routes
        Route::get('/{campaign}/pages', function (Domain\Campaign\Models\Campaign $campaign) {
            return view('campaigns.pages', compact('campaign'));
        })->name('pages');
        
        Route::get('/{campaign}/pages/{page}', function (Domain\Campaign\Models\Campaign $campaign, Domain\CampaignPage\Models\CampaignPage $page) {
            // Verify the page belongs to the campaign
            if ($page->campaign_id !== $campaign->id) {
                abort(404);
            }
            return view('campaigns.page-show', compact('campaign', 'page'));
        })->name('page.show');
    });
    
    // Campaign Frame routes
    Route::prefix('campaign-frames')->name('campaign-frames.')->group(function () {
        Route::get('/', [App\Http\Controllers\CampaignFrameController::class, 'index'])->name('index');
        Route::get('/browse', [App\Http\Controllers\CampaignFrameController::class, 'browse'])->name('browse');
        Route::get('/create', [App\Http\Controllers\CampaignFrameController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\CampaignFrameController::class, 'store'])->name('store');
        Route::get('/{campaign_frame}', [App\Http\Controllers\CampaignFrameController::class, 'show'])->name('show');
        Route::get('/{campaign_frame}/edit', [App\Http\Controllers\CampaignFrameController::class, 'edit'])->name('edit');
        Route::put('/{campaign_frame}', [App\Http\Controllers\CampaignFrameController::class, 'update'])->name('update');
        Route::delete('/{campaign_frame}', [App\Http\Controllers\CampaignFrameController::class, 'destroy'])->name('destroy');
    });
    
    // Campaign invite routes (separate from auth to allow invite sharing)
    Route::get('/join/{invite_code}', [App\Http\Controllers\CampaignController::class, 'showJoin'])->name('campaigns.invite');
    
    // Authenticated room routes (creation, management)
    Route::prefix('rooms')->name('rooms.')->group(function () {
        Route::get('/', [App\Http\Controllers\RoomController::class, 'index'])->name('index');
        Route::get('/create', [App\Http\Controllers\RoomController::class, 'create'])->name('create');
        Route::post('/', [App\Http\Controllers\RoomController::class, 'store'])->name('store');
        Route::delete('/{room:invite_code}/leave', [App\Http\Controllers\RoomController::class, 'leave'])->name('leave');
        Route::delete('/{room:invite_code}/kick/{participant}', [App\Http\Controllers\RoomController::class, 'kickParticipant'])->name('kick');
        Route::delete('/{room:invite_code}', [App\Http\Controllers\RoomController::class, 'destroy'])->name('destroy');
    });
});

// Public room routes (accessible without auth for non-campaign rooms)
Route::prefix('rooms')->name('rooms.')->group(function () {
    Route::get('/{room:invite_code}', [App\Http\Controllers\RoomController::class, 'show'])->name('show');
    Route::get('/{room:invite_code}/session', [App\Http\Controllers\RoomController::class, 'session'])->name('session');
    Route::post('/{room:invite_code}/join', [App\Http\Controllers\RoomController::class, 'join'])->name('join');
});

// Room invite and viewer routes (consistent pattern)
Route::get('/rooms/join/{invite_code}', [App\Http\Controllers\RoomController::class, 'showJoin'])->name('rooms.invite');
Route::get('/rooms/watch/{viewer_code}', [App\Http\Controllers\RoomController::class, 'viewer'])->name('rooms.viewer');
Route::post('/rooms/watch/{viewer_code}', [App\Http\Controllers\RoomController::class, 'viewerPassword'])->name('rooms.viewer.password');

// Character routes (public)
Route::get('/characters', [App\Http\Controllers\CharacterBuilderController::class, 'index'])->name('characters');
Route::get('/character-builder', [App\Http\Controllers\CharacterBuilderController::class, 'create'])->name('character-builder');
Route::get('/character-builder/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'edit'])->name('character-builder.edit');
Route::get('/character/{public_key}', [App\Http\Controllers\CharacterBuilderController::class, 'show'])->name('character.show');

// API routes for character data
Route::get('/api/character/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'apiShow'])->name('api.character.show');
Route::delete('/api/character/{character_key}', [App\Http\Controllers\CharacterBuilderController::class, 'apiDestroy'])->name('api.character.destroy');

Route::get('/range-check', function () {
    return view('range-check');
})->name('range-check');

Route::get('/actual-plays', function () {
    $actual_plays_data = json_decode(file_get_contents(resource_path('json/actual-plays.json')), true);
    return view('actual-plays', compact('actual_plays_data'));
})->name('actual-plays');

// WebRTC ICE configuration route
Route::get('/api/webrtc/ice-config', [App\Http\Controllers\Api\WebRTCController::class, 'iceConfig'])->name('api.webrtc.ice-config');

// API routes for room transcripts, recordings, and consent
Route::prefix('api/rooms')->name('api.rooms.')->group(function () {
    Route::post('/{room}/transcripts', [App\Http\Controllers\Api\RoomTranscriptController::class, 'store'])->name('transcripts.store');
    Route::get('/{room}/transcripts', [App\Http\Controllers\Api\RoomTranscriptController::class, 'index'])->name('transcripts.index');
    Route::post('/{room}/stt-consent', [App\Http\Controllers\Api\RoomConsentController::class, 'updateSttConsent'])->name('stt-consent.update');
    Route::get('/{room}/stt-consent', [App\Http\Controllers\Api\RoomConsentController::class, 'getSttConsentStatus'])->name('stt-consent.status');
    Route::post('/{room}/recording-consent', [App\Http\Controllers\Api\RoomConsentController::class, 'updateRecordingConsent'])->name('recording-consent.update');
    Route::get('/{room}/recording-consent', [App\Http\Controllers\Api\RoomConsentController::class, 'getRecordingConsentStatus'])->name('recording-consent.status');
    Route::post('/{room}/recordings', [App\Http\Controllers\Api\RoomRecordingController::class, 'store'])->name('recordings.store');
    Route::get('/{room}/recordings', [App\Http\Controllers\Api\RoomRecordingController::class, 'index'])->name('recordings.index');
    Route::get('/{room}/recordings/{recording}/download', [App\Http\Controllers\Api\RoomRecordingController::class, 'download'])->name('recordings.download');
    Route::post('/{room}/recordings/presign-wasabi', [App\Http\Controllers\Api\RoomRecordingController::class, 'presignWasabi'])->name('recordings.presign-wasabi');
    Route::post('/{room}/recordings/confirm-wasabi', [App\Http\Controllers\Api\RoomRecordingController::class, 'confirmWasabiUpload'])->name('recordings.confirm-wasabi');
    Route::post('/{room}/recordings/upload-google-drive', [App\Http\Controllers\Api\RoomRecordingController::class, 'uploadGoogleDrive'])->name('recordings.upload-google-drive');
    Route::post('/{room}/recordings/google-drive-upload-url', [App\Http\Controllers\Api\RoomRecordingController::class, 'generateGoogleDriveUploadUrl'])->name('recordings.google-drive-upload-url');
    Route::post('/{room}/recordings/confirm-google-drive', [App\Http\Controllers\Api\RoomRecordingController::class, 'confirmGoogleDriveUpload'])->name('recordings.confirm-google-drive');
});

// S3 Multipart upload endpoints (for better resilience with large files)
Route::prefix('api/uploads/s3/multipart')->name('api.uploads.s3.multipart.')->middleware('auth')->group(function () {
    Route::post('/create', [App\Http\Controllers\Api\S3MultipartController::class, 'create'])->name('create');
    Route::post('/sign', [App\Http\Controllers\Api\S3MultipartController::class, 'signPart'])->name('sign');
    Route::post('/complete', [App\Http\Controllers\Api\S3MultipartController::class, 'complete'])->name('complete');
    Route::post('/abort', [App\Http\Controllers\Api\S3MultipartController::class, 'abort'])->name('abort');
});

// Google Drive OAuth routes
Route::prefix('google-drive')->name('google-drive.')->middleware('auth')->group(function () {
    Route::get('/authorize', [App\Http\Controllers\GoogleDriveController::class, 'authorize'])->name('authorize');
    Route::get('/callback', [App\Http\Controllers\GoogleDriveController::class, 'callback'])->name('callback');
    Route::post('/disconnect', [App\Http\Controllers\GoogleDriveController::class, 'disconnect'])->name('disconnect');
});

// Storage account management
Route::get('/storage-accounts', App\Livewire\StorageAccountDashboard::class)->name('storage-accounts')->middleware('auth');

Route::get('/video-library', App\Livewire\VideoLibrary::class)->name('video-library')->middleware('auth');

// API routes for recording downloads and STT config
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/rooms/{room}/recordings/{recording}/download', [App\Http\Controllers\Api\RecordingDownloadController::class, 'download'])
        ->name('rooms.recordings.download')
        ->middleware('auth');
    Route::get('/rooms/{room}/stt-config', [App\Http\Controllers\Api\SttConfigController::class, 'getConfig'])
        ->name('rooms.stt-config')
        ->middleware('auth');
    Route::post('/assemblyai/token', [App\Http\Controllers\AssemblyAIController::class, 'generateToken'])
        ->name('assemblyai.token')
        ->middleware('auth');
});

// Storage account setup
Route::get('/wasabi/connect', App\Livewire\WasabiAccountSetup::class)->name('wasabi.connect')->middleware('auth');
Route::get('/assemblyai/connect', App\Livewire\AssemblyAIAccountSetup::class)->name('assemblyai.connect')->middleware('auth');

// Simple test route for debugging Livewire
Route::get('/simple-test', function () {
    return view('simple-test-page');
});

// Character image upload routes
require __DIR__ . '/character-image-upload.php';