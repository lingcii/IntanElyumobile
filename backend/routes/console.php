<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --- Technique 6: Materialized Views ---
// Refresh the leaderboard_cache table every 5 minutes
Schedule::command('leaderboard:refresh')->everyFiveMinutes()->withoutOverlapping();

// Automatically default untouched destination reviews to 5 stars after 2-3 days
Schedule::call(function () {
    \App\Http\Controllers\Tourist\ItineraryController::processUntouchedVisitReviews(2);
})->name('reviews:auto-default-untouched')->hourly()->withoutOverlapping();
