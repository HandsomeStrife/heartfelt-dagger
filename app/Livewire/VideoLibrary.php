<?php

declare(strict_types=1);

namespace App\Livewire;

use Carbon\Carbon;
use Domain\Room\Data\RoomRecordingData;
use Domain\Room\Repositories\RoomRecordingRepository;
use Domain\User\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class VideoLibrary extends Component
{
    // Display properties
    public Collection $recordings;

    public Collection $groupedRecordings;

    public array $storageAnalytics;

    // Filter properties
    public string $viewMode = 'list'; // 'list', 'grid', 'rooms'

    public string $searchQuery = '';

    public string $selectedProvider = 'all'; // 'all', 'wasabi', 'google_drive', 'local'

    public string $selectedStatus = 'ready'; // 'all', 'ready', 'processing', 'failed'

    public string $selectedDateRange = 'all'; // 'all', 'today', 'week', 'month', 'custom'

    public string $customStartDate = '';

    public string $customEndDate = '';

    // UI state
    public ?int $selectedRecordingId = null;

    public bool $showAnalytics = false;

    public bool $showFilters = false;

    public function mount(): void
    {
        $this->loadRecordings();
        $this->loadStorageAnalytics();
    }

    public function loadRecordings(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $repository = new RoomRecordingRepository;

        // Apply filters
        $recordings = $this->applyFilters($repository, $user);

        $this->recordings = $recordings;
        $this->groupedRecordings = $repository->getGroupedByRoomForUser($user);
    }

    public function loadStorageAnalytics(): void
    {
        $user = Auth::user();

        if (! $user instanceof User) {
            return;
        }

        $repository = new RoomRecordingRepository;
        $this->storageAnalytics = $repository->getStorageAnalyticsForUser($user);
    }

    private function applyFilters(RoomRecordingRepository $repository, User $user): Collection
    {
        // Start with search if provided
        if (! empty($this->searchQuery)) {
            $recordings = $repository->searchForUser($this->searchQuery, $user);
        } else {
            $recordings = $repository->getByUser($user);
        }

        // Apply provider filter
        if ($this->selectedProvider !== 'all') {
            $recordings = $recordings->filter(function (RoomRecordingData $recording) {
                return $recording->provider === $this->selectedProvider;
            });
        }

        // Apply status filter
        if ($this->selectedStatus !== 'all') {
            if ($this->selectedStatus === 'ready') {
                $recordings = $recordings->filter(function (RoomRecordingData $recording) {
                    return in_array($recording->status, ['ready', 'uploaded']);
                });
            } else {
                $recordings = $recordings->filter(function (RoomRecordingData $recording) {
                    return $recording->status === $this->selectedStatus;
                });
            }
        }

        // Apply date range filter
        if ($this->selectedDateRange !== 'all') {
            $recordings = $this->filterByDateRange($recordings);
        }

        return $recordings;
    }

    private function filterByDateRange(Collection $recordings): Collection
    {
        $now = Carbon::now();

        switch ($this->selectedDateRange) {
            case 'today':
                $start = $now->copy()->startOfDay();
                $end = $now->copy()->endOfDay();
                break;
            case 'week':
                $start = $now->copy()->startOfWeek();
                $end = $now->copy()->endOfWeek();
                break;
            case 'month':
                $start = $now->copy()->startOfMonth();
                $end = $now->copy()->endOfMonth();
                break;
            case 'custom':
                if (empty($this->customStartDate) || empty($this->customEndDate)) {
                    return $recordings;
                }
                $start = Carbon::parse($this->customStartDate)->startOfDay();
                $end = Carbon::parse($this->customEndDate)->endOfDay();
                break;
            default:
                return $recordings;
        }

        return $recordings->filter(function (RoomRecordingData $recording) use ($start, $end) {
            $recordingDate = Carbon::createFromTimestamp($recording->started_at_ms / 1000);

            return $recordingDate->greaterThanOrEqualTo($start) && $recordingDate->lessThanOrEqualTo($end);
        });
    }

    public function updatedSearchQuery(): void
    {
        $this->loadRecordings();
    }

    public function updatedSelectedProvider(): void
    {
        $this->loadRecordings();
    }

    public function updatedSelectedStatus(): void
    {
        $this->loadRecordings();
    }

    public function updatedSelectedDateRange(): void
    {
        $this->loadRecordings();
    }

    public function updatedCustomStartDate(): void
    {
        if ($this->selectedDateRange === 'custom') {
            $this->loadRecordings();
        }
    }

    public function updatedCustomEndDate(): void
    {
        if ($this->selectedDateRange === 'custom') {
            $this->loadRecordings();
        }
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = $mode;
    }

    public function toggleFilters(): void
    {
        $this->showFilters = ! $this->showFilters;
    }

    public function toggleAnalytics(): void
    {
        $this->showAnalytics = ! $this->showAnalytics;
        if ($this->showAnalytics) {
            $this->loadStorageAnalytics();
        }
    }

    public function selectRecording(?int $recordingId): void
    {
        $this->selectedRecordingId = $recordingId;
    }

    public function clearFilters(): void
    {
        $this->searchQuery = '';
        $this->selectedProvider = 'all';
        $this->selectedStatus = 'ready';
        $this->selectedDateRange = 'all';
        $this->customStartDate = '';
        $this->customEndDate = '';
        $this->loadRecordings();
    }

    public function getSelectedRecordingProperty(): ?RoomRecordingData
    {
        if (! $this->selectedRecordingId) {
            return null;
        }

        return $this->recordings->firstWhere('id', $this->selectedRecordingId);
    }

    public function getProviderOptionsProperty(): array
    {
        return [
            'all' => 'All Providers',
            'wasabi' => 'Wasabi',
            'google_drive' => 'Google Drive',
            'local' => 'Local Storage',
        ];
    }

    public function getStatusOptionsProperty(): array
    {
        return [
            'all' => 'All Status',
            'ready' => 'Ready',
            'processing' => 'Processing',
            'failed' => 'Failed',
        ];
    }

    public function getDateRangeOptionsProperty(): array
    {
        return [
            'all' => 'All Time',
            'today' => 'Today',
            'week' => 'This Week',
            'month' => 'This Month',
            'custom' => 'Custom Range',
        ];
    }

    public function playRecording(int $recordingId): void
    {
        \Log::info('Play recording called', ['recording_id' => $recordingId]);
        // This will be handled by the modal's video player
        $this->selectRecording($recordingId);
    }

    public function generateStreamUrl(int $recordingId)
    {
        \Log::info('Generate stream URL called', ['recording_id' => $recordingId]);
        $user = Auth::user();

        if (! $user instanceof User) {
            session()->flash('error', 'You must be logged in to stream recordings.');

            return null;
        }

        $repository = new RoomRecordingRepository;
        $recording = $repository->findByIdForUser($recordingId, $user);

        if (! $recording || ! in_array($recording->status, ['ready', 'uploaded'])) {
            session()->flash('error', 'Recording not available for streaming.');

            return null;
        }

        if ($recording->provider === 'wasabi') {
            $url = $this->getWasabiStreamUrl($recording);
            \Log::info('Stream URL generated', ['recording_id' => $recordingId, 'url_length' => $url ? strlen($url) : 0]);

            return $url;
        } else {
            session()->flash('error', 'Streaming not supported for this storage provider.');

            return null;
        }
    }

    private function getWasabiStreamUrl(RoomRecordingData $recording): ?string
    {
        try {
            $user = Auth::user();
            $storageAccount = $user->storageAccounts()
                ->where('provider', 'wasabi')
                ->where('is_active', true)
                ->first();

            if (! $storageAccount) {
                session()->flash('error', 'No active Wasabi storage account found.');

                return null;
            }

            $wasabiService = new \Domain\Room\Services\WasabiS3Service($storageAccount);

            // Generate a presigned URL for streaming (4 hours)
            $streamResult = $wasabiService->generatePresignedDownloadUrl($recording->provider_file_id, 60 * 4);

            if (is_array($streamResult) && isset($streamResult['download_url'])) {
                return $streamResult['download_url'];
            }

            return null;

        } catch (\Exception $e) {
            \Log::error('Stream URL generation failed', [
                'recording_id' => $recording->id,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function generateThumbnailForRecording(int $recordingId)
    {
        \Log::info('Generate thumbnail called', ['recording_id' => $recordingId]);

        // This will be handled by JavaScript - just return the stream URL
        return $this->generateStreamUrl($recordingId);
    }

    public function downloadRecording(int $recordingId)
    {
        \Log::info('Download recording called', ['recording_id' => $recordingId]);
        $user = Auth::user();

        if (! $user instanceof User) {
            \Log::error('Download failed: User not authenticated', ['recording_id' => $recordingId]);
            session()->flash('error', 'You must be logged in to download recordings.');

            return;
        }

        \Log::info('User authenticated for download', ['user_id' => $user->id, 'recording_id' => $recordingId]);

        $repository = new RoomRecordingRepository;
        $recording = $repository->findByIdForUser($recordingId, $user);

        if (! $recording) {
            \Log::error('Download failed: Recording not found', ['recording_id' => $recordingId, 'user_id' => $user->id]);
            session()->flash('error', 'Recording not found.');

            return;
        }

        \Log::info('Recording found', [
            'recording_id' => $recordingId,
            'status' => $recording->status,
            'provider' => $recording->provider,
            'provider_file_id' => $recording->provider_file_id,
        ]);

        if (! in_array($recording->status, ['ready', 'uploaded'])) {
            \Log::error('Download failed: Recording not ready', ['recording_id' => $recordingId, 'status' => $recording->status]);
            session()->flash('error', 'Recording not available for download.');

            return;
        }

        // Generate download URL based on provider and redirect
        if ($recording->provider === 'wasabi') {
            \Log::info('Attempting Wasabi download', ['recording_id' => $recordingId]);
            $url = $this->getWasabiDownloadUrl($recording);
            if ($url) {
                \Log::info('Redirecting to Wasabi download URL', ['recording_id' => $recordingId]);

                return $this->redirect($url);
            }
        } elseif ($recording->provider === 'google_drive') {
            \Log::info('Attempting Google Drive download', ['recording_id' => $recordingId]);
            $url = $this->getGoogleDriveDownloadUrl($recording);
            if ($url) {
                \Log::info('Redirecting to Google Drive download URL', ['recording_id' => $recordingId]);

                return $this->redirect($url);
            }
        } else {
            \Log::error('Download failed: Unsupported provider', ['recording_id' => $recordingId, 'provider' => $recording->provider]);
            session()->flash('error', 'Download not supported for this storage provider.');
        }
    }

    private function getWasabiDownloadUrl(RoomRecordingData $recording): ?string
    {
        try {
            \Log::info('Getting Wasabi download URL', ['recording_id' => $recording->id]);

            // Find the user's Wasabi storage account
            $user = Auth::user();
            $storageAccount = $user->storageAccounts()
                ->where('provider', 'wasabi')
                ->where('is_active', true)
                ->first();

            if (! $storageAccount) {
                \Log::error('No active Wasabi storage account found', ['user_id' => $user->id]);
                session()->flash('error', 'No active Wasabi storage account found.');

                return null;
            }

            \Log::info('Wasabi storage account found', ['storage_account_id' => $storageAccount->id]);

            $wasabiService = new \Domain\Room\Services\WasabiS3Service($storageAccount);
            \Log::info('Wasabi service created, generating presigned URL', ['provider_file_id' => $recording->provider_file_id]);

            // Generate presigned URL with download headers
            $filename = $recording->filename;
            \Log::info('Using download filename', ['filename' => $filename]);

            $downloadResult = $wasabiService->generatePresignedDownloadUrl($recording->provider_file_id, 3600, [
                'ResponseContentDisposition' => 'attachment; filename="'.$filename.'"',
            ]); // 1 hour expiry

            \Log::info('Wasabi presigned URL result', [
                'url_generated' => ! empty($downloadResult),
                'url_is_string' => is_string($downloadResult),
                'url_type' => gettype($downloadResult),
                'url_value' => $downloadResult,
            ]);

            // Handle both string and array responses
            if (is_string($downloadResult)) {
                $downloadUrl = $downloadResult;
            } elseif (is_array($downloadResult) && isset($downloadResult['download_url'])) {
                $downloadUrl = $downloadResult['download_url'];
            } else {
                \Log::error('Failed to generate Wasabi download URL', ['downloadResult' => $downloadResult]);
                session()->flash('error', 'Failed to generate download URL.');

                return null;
            }

            if (! $downloadUrl || ! is_string($downloadUrl)) {
                \Log::error('Invalid Wasabi download URL format', ['downloadUrl' => $downloadUrl]);
                session()->flash('error', 'Invalid download URL format.');

                return null;
            }

            \Log::info('Wasabi download URL successfully generated', ['recording_id' => $recording->id, 'url_length' => strlen($downloadUrl)]);

            return $downloadUrl;
        } catch (\Exception $e) {
            \Log::error('Exception in getWasabiDownloadUrl', [
                'recording_id' => $recording->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            session()->flash('error', 'Failed to generate download link: '.$e->getMessage());

            return null;
        }
    }

    private function getGoogleDriveDownloadUrl(RoomRecordingData $recording): ?string
    {
        try {
            // Return the download API endpoint URL
            return route('api.rooms.recordings.download', [
                'room' => $recording->room_id,
                'recording' => $recording->id,
            ]);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to download from Google Drive: '.$e->getMessage());

            return null;
        }
    }

    // Helper methods for the JavaScript functions
    public function formatBytes(int $bytes): string
    {
        if ($bytes === 0) {
            return '0 B';
        }

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));

        return round($bytes / pow(1024, $i), 2).' '.$units[$i];
    }

    public function formatDuration(int $ms): string
    {
        if ($ms === 0) {
            return '0:00';
        }

        $seconds = (int) floor($ms / 1000);
        $minutes = (int) floor($seconds / 60);
        $hours = (int) floor($minutes / 60);

        if ($hours > 0) {
            return $hours.':'.str_pad((string) ($minutes % 60), 2, '0', STR_PAD_LEFT).':'.str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);
        } else {
            return $minutes.':'.str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);
        }
    }

    public function render()
    {
        return view('livewire.video-library');
    }
}
