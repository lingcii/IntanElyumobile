<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UploadApkToR2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'apk:upload-r2 {--file= : Path to specific APK file to upload}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upload the compiled mobile release APK directly to Cloudflare R2 mirror for live website downloads.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("🚀 Starting APK Upload to Cloudflare R2...");

        $customFile = $this->option('file');
        $candidatePaths = [];

        if ($customFile) {
            $candidatePaths[] = $customFile;
        }

        $baseDir = dirname(base_path());
        $candidatePaths = array_merge($candidatePaths, [
            $baseDir . '/frontend/Mobile/android/app/build/outputs/apk/release/app-release.apk',
            $baseDir . '/frontend/Mobile/public/downloads/intan-elyu.apk',
            $baseDir . '/frontend/Mobile/app-release.apk',
            $baseDir . '/frontend/Mobile/IntanElyu-v1.4.1-release.apk',
            $baseDir . '/frontend/Mobile/IntanElyu-v1.4.0-release.apk',
            $baseDir . '/frontend/Mobile/android/app/build/outputs/apk/debug/app-debug.apk',
        ]);

        $selectedApk = null;
        foreach ($candidatePaths as $candidate) {
            if (file_exists($candidate) && filesize($candidate) > 10 * 1024 * 1024) {
                $selectedApk = $candidate;
                break;
            }
        }

        if (!$selectedApk || !file_exists($selectedApk)) {
            $this->error("❌ Could not find any valid APK file (>10MB). Looked in:");
            foreach ($candidatePaths as $path) {
                $this->line("   - " . $path);
            }
            return 1;
        }

        $fileSizeMB = round(filesize($selectedApk) / (1024 * 1024), 2);
        $this->info("📦 Selected APK: {$selectedApk}");
        $this->info("📏 File Size: {$fileSizeMB} MB (" . filesize($selectedApk) . " bytes)");

        $targetKey = 'apks/intan-elyu.apk';
        $this->info("☁️  Uploading to Cloudflare R2 target: {$targetKey}...");

        try {
            $disk = Storage::disk('r2');
            $stream = fopen($selectedApk, 'rb');
            if (!$stream) {
                $this->error("❌ Failed to open read stream on {$selectedApk}");
                return 1;
            }

            // Stream upload with appropriate headers
            $success = $disk->put($targetKey, $stream, [
                'visibility' => 'public',
                'ContentType' => 'application/vnd.android.package-archive',
                'CacheControl' => 'no-cache, no-store, must-revalidate',
            ]);

            if (is_resource($stream)) {
                fclose($stream);
            }

            if ($success) {
                $publicUrl = rtrim(config('filesystems.disks.r2.url'), '/') . '/' . $targetKey;
                $this->info("✅ SUCCESS! Fresh APK uploaded to Cloudflare R2.");
                $this->line("🌐 Public Download URL: {$publicUrl}");
                $this->line("ℹ️  Web visitors will now receive the updated v1.4.0 APK with full offline mode.");
                return 0;
            } else {
                $this->error("❌ R2 upload returned false. Check Cloudflare R2 credentials or permissions.");
                return 1;
            }
        } catch (\Exception $e) {
            $this->error("❌ Error uploading to R2: " . $e->getMessage());
            return 1;
        }
    }
}
