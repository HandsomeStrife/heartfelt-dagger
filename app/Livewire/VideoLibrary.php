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
        
        if (!$user instanceof User) {
            return;
        }

        $repository = new RoomRecordingRepository();
        
        // Apply filters
        $recordings = $this->applyFilters($repository, $user);
        
        $this->recordings = $recordings;
        $this->groupedRecordings = $repository->getGroupedByRoomForUser($user);
    }

    public function loadStorageAnalytics(): void
    {
        $user = Auth::user();
        
        if (!$user instanceof User) {
            return;
        }

        $repository = new RoomRecordingRepository();
        $this->storageAnalytics = $repository->getStorageAnalyticsForUser($user);
    }

    private function applyFilters(RoomRecordingRepository $repository, User $user): Collection
    {
        // Start with search if provided
        if (!empty($this->searchQuery)) {
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
        $this->showFilters = !$this->showFilters;
    }

    public function toggleAnalytics(): void
    {
        $this->showAnalytics = !$this->showAnalytics;
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
        if (!$this->selectedRecordingId) {
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
        // This will be handled by the modal's video player
        $this->selectRecording($recordingId);
    }

    public function downloadRecording(int $recordingId): void
    {
        $user = Auth::user();
        
        if (!$user instanceof User) {
            return;
        }

        $repository = new RoomRecordingRepository();
        $recording = $repository->findByIdForUser($recordingId, $user);

        if (!$recording || !in_array($recording->status, ['ready', 'uploaded'])) {
            session()->flash('error', 'Recording not available for download.');
            return;
        }

        // Generate download URL based on provider
        if ($recording->provider === 'wasabi') {
            $this->downloadFromWasabi($recording);
        } elseif ($recording->provider === 'google_drive') {
            $this->downloadFromGoogleDrive($recording);
        } else {
            session()->flash('error', 'Download not supported for this storage provider.');
        }
    }

    private function downloadFromWasabi(RoomRecordingData $recording): void
    {
        try {
            // Find the user's Wasabi storage account
            $user = Auth::user();
            $storageAccount = $user->storageAccounts()
                ->where('provider', 'wasabi')
                ->where('is_active', true)
                ->first();

            if (!$storageAccount) {
                session()->flash('error', 'No active Wasabi storage account found.');
                return;
            }

            $wasabiService = new \Domain\Room\Services\WasabiS3Service($storageAccount);
            $downloadUrl = $wasabiService->generatePresignedDownloadUrl($recording->provider_file_id, 3600); // 1 hour expiry

            $this->redirect($downloadUrl);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to generate download link: ' . $e->getMessage());
        }
    }

    private function downloadFromGoogleDrive(RoomRecordingData $recording): void
    {
        try {
            // Redirect to the download API endpoint
            $this->redirect(route('api.rooms.recordings.download', [
                'room' => $recording->room_id,
                'recording' => $recording->id
            ]));
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to download from Google Drive: ' . $e->getMessage());
        }
    }

    // Helper methods for the JavaScript functions
    public function formatBytes(int $bytes): string
    {
        if ($bytes === 0) return '0 B';
        
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes) / log(1024));
        
        return round($bytes / pow(1024, $i), 2) . ' ' . $units[$i];
    }

    public function formatDuration(int $ms): string
    {
        if ($ms === 0) return '0:00';
        
        $seconds = (int) floor($ms / 1000);
        $minutes = (int) floor($seconds / 60);
        $hours = (int) floor($minutes / 60);
        
        if ($hours > 0) {
            return $hours . ':' . str_pad((string)($minutes % 60), 2, '0', STR_PAD_LEFT) . ':' . str_pad((string)($seconds % 60), 2, '0', STR_PAD_LEFT);
        } else {
            return $minutes . ':' . str_pad((string)($seconds % 60), 2, '0', STR_PAD_LEFT);
        }
    }

    public function render()
    {
        return view('livewire.video-library');
    }
}
