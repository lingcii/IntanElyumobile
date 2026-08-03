<?php

namespace App\Console\Commands;

use App\Models\TouristSpot;
use App\Models\TouristSpotImage;
use App\Helpers\ImageCompressor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ScanR2Images extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spots:scan-r2 {--repair : Automatically fix and update Railway database photo_urls to point directly to Cloudflare R2}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan and verify all tourist spot images in Cloudflare R2 bucket and sync with Railway MySQL database.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🔍 Starting Cloudflare R2 & Railway Database Image Scan...");
        $autoRepair = $this->option('repair');

        $spots = TouristSpot::all();
        $totalSpots = $spots->count();
        $verifiedCount = 0;
        $repairedCount = 0;
        $missingCount = 0;

        $r2PublicUrl = rtrim(env('CLOUDFLARE_R2_URL', 'https://pub-268a50c87a9249ccbf90d35e77ddc65b.r2.dev'), '/');

        foreach ($spots as $spot) {
            $rawPhotoUrl = $spot->getRawOriginal('photo_url');
            if (empty($rawPhotoUrl)) {
                $this->warn("⚠️  Spot #{$spot->id} ({$spot->name}): No photo_url set.");
                $missingCount++;
                continue;
            }

            // Determine R2 key path
            $r2Path = null;
            if (preg_match('#(tourist_spots/[^/]+)#i', $rawPhotoUrl, $matches)) {
                $r2Path = $matches[1];
            } elseif (preg_match('#(spot_[a-z0-9_]+\.(?:jpg|jpeg|png|webp|gif))#i', $rawPhotoUrl, $matches)) {
                $r2Path = 'tourist_spots/' . $matches[1];
            }

            $existsOnR2 = false;
            if ($r2Path) {
                try {
                    $existsOnR2 = Storage::disk('r2')->exists($r2Path);
                } catch (\Throwable $e) {
                    $this->error("R2 Error checking {$r2Path}: " . $e->getMessage());
                }
            }

            if ($existsOnR2) {
                $fullR2Url = $r2PublicUrl . '/' . ltrim($r2Path, '/');
                if ($rawPhotoUrl !== $fullR2Url && $autoRepair) {
                    $spot->update(['photo_url' => $fullR2Url]);
                    $this->info("✅ Repaired Spot #{$spot->id} ({$spot->name}) -> {$fullR2Url}");
                    $repairedCount++;
                } else {
                    $this->info("✅ Verified Spot #{$spot->id} ({$spot->name}) on Cloudflare R2");
                    $verifiedCount++;
                }
            } else {
                $this->warn("⚠️ Spot #{$spot->id} ({$spot->name}): Image {$rawPhotoUrl} not found on Cloudflare R2 disk.");
                $missingCount++;
            }
        }

        // Flush public map & trending spots cache so changes are immediately visible
        Cache::forget('map:public:spots');
        Cache::forget('trending:top:5');
        Cache::forget('trending:top:10');
        Cache::forget('trending:top:50');

        $this->newLine();
        $this->info("=== CLOUDFLARE R2 & RAILWAY DB SCAN SUMMARY ===");
        $this->line("Total Spots Checked : {$totalSpots}");
        $this->line("Verified in R2      : {$verifiedCount}");
        $this->line("Repaired in DB      : {$repairedCount}");
        $this->line("Missing/Unverified  : {$missingCount}");

        return Command::SUCCESS;
    }
}
