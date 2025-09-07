<?php

use App\Jobs\FinalizeStaleRecordings;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule the job to finalize stale recordings every 30 seconds
Schedule::job(new FinalizeStaleRecordings())->everyThirtySeconds();
