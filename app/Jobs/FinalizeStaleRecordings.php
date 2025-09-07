<?php

namespace App\Jobs;

use Domain\Room\Actions\FinalizeRecording;
use Domain\Room\Models\RoomRecording;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class FinalizeStaleRecordings implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('Starting stale recordings finalization job');

        $staleRecordings = RoomRecording::needingFinalization()->get();

        Log::info('Found stale recordings to finalize', [
            'count' => $staleRecordings->count()
        ]);

        $finalizeAction = new FinalizeRecording();
        $successCount = 0;
        $failureCount = 0;

        foreach ($staleRecordings as $recording) {
            Log::info('Attempting to finalize stale recording', [
                'recording_id' => $recording->id,
                'room_id' => $recording->room_id,
                'user_id' => $recording->user_id,
                'multipart_upload_id' => $recording->multipart_upload_id,
                'last_updated' => $recording->updated_at->toISOString()
            ]);

            if ($finalizeAction->execute($recording)) {
                $successCount++;
            } else {
                $failureCount++;
            }
        }

        Log::info('Completed stale recordings finalization job', [
            'total_processed' => $staleRecordings->count(),
            'successful' => $successCount,
            'failed' => $failureCount
        ]);
    }
}
