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
    protected $signature = 'apk:upload-r2 {--file= : Path to specific APK file to upload} {--cleanup-only : Only abort incomplete multipart uploads without uploading}';

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
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        $targetKey = 'apks/intan-elyu.apk';

        if ($this->option('cleanup-only')) {
            $this->info("🧹 Cleaning up incomplete multipart uploads on Cloudflare R2...");
            try {
                $aborted = $this->abortStaleMultipartUploads(Storage::disk('r2'), $targetKey);
                $this->info("✅ Cleaned up {$aborted} incomplete multipart upload(s).");
                return 0;
            } catch (\Exception $e) {
                $this->error("❌ Error cleaning up multipart uploads: " . $e->getMessage());
                return 1;
            }
        }

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
        ]);

        $existingApks = [];
        foreach ($candidatePaths as $candidate) {
            if (file_exists($candidate) && filesize($candidate) > 10 * 1024 * 1024) {
                $existingApks[] = [
                    'path' => $candidate,
                    'mtime' => filemtime($candidate),
                ];
            }
        }

        // Sort by most recent modification time
        usort($existingApks, function ($a, $b) {
            return $b['mtime'] <=> $a['mtime'];
        });

        $selectedApk = !empty($existingApks) ? $existingApks[0]['path'] : null;

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
            $this->abortStaleMultipartUploads($disk, $targetKey);

            $stream = fopen($selectedApk, 'rb');
            if (!$stream) {
                $this->error("❌ Failed to open read stream on {$selectedApk}");
                return 1;
            }

            $success = false;
            if (method_exists($disk, 'getClient')) {
                $client = $disk->getClient();
                $bucket = config('filesystems.disks.r2.bucket', 'intan-elyu-media');
                $partSize = 8 * 1024 * 1024; // 8 MB parts
                $fileSize = filesize($selectedApk);
                $totalParts = (int) ceil($fileSize / $partSize);

                $createRes = $client->createMultipartUpload([
                    'Bucket' => $bucket,
                    'Key' => $targetKey,
                    'ContentType' => 'application/vnd.android.package-archive',
                    'CacheControl' => 'no-cache, no-store, must-revalidate',
                ]);
                $uploadId = $createRes['UploadId'];
                $parts = [];

                for ($partNum = 1; $partNum <= $totalParts; $partNum++) {
                    $chunk = fread($stream, $partSize);
                    $attempts = 0;
                    $uploadedPart = false;
                    while ($attempts < 4 && !$uploadedPart) {
                        $attempts++;
                        try {
                            $res = $client->uploadPart([
                                'Bucket' => $bucket,
                                'Key' => $targetKey,
                                'UploadId' => $uploadId,
                                'PartNumber' => $partNum,
                                'Body' => $chunk,
                            ]);
                            $parts[] = [
                                'PartNumber' => $partNum,
                                'ETag' => $res['ETag'],
                            ];
                            $uploadedPart = true;
                            $pct = round(($partNum / $totalParts) * 100);
                            $this->line("   ↳ Part {$partNum}/{$totalParts} ({$pct}%) uploaded");
                        } catch (\Throwable $partErr) {
                            $this->warn("   ⚠️ Part {$partNum} attempt {$attempts} failed: " . $partErr->getMessage());
                            if ($attempts >= 4) {
                                throw $partErr;
                            }
                            sleep(2);
                        }
                    }
                }

                $client->completeMultipartUpload([
                    'Bucket' => $bucket,
                    'Key' => $targetKey,
                    'UploadId' => $uploadId,
                    'MultipartUpload' => ['Parts' => $parts],
                ]);
                $success = true;
            } else {
                // Stream upload with appropriate headers
                $success = $disk->put($targetKey, $stream, [
                    'visibility' => 'public',
                    'ContentType' => 'application/vnd.android.package-archive',
                    'CacheControl' => 'no-cache, no-store, must-revalidate',
                ]);
            }

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
                $this->abortStaleMultipartUploads($disk, $targetKey);
                $this->error("❌ R2 upload returned false. Check Cloudflare R2 credentials or permissions.");
                return 1;
            }
        } catch (\Exception $e) {
            try {
                $this->abortStaleMultipartUploads(Storage::disk('r2'), $targetKey);
            } catch (\Throwable $ignore) {}
            $this->error("❌ Error uploading to R2: " . $e->getMessage());
            return 1;
        }
    }

    protected function abortStaleMultipartUploads($disk, string $targetKey): int
    {
        if (!method_exists($disk, 'getClient')) {
            return 0;
        }
        $client = $disk->getClient();
        $bucket = config('filesystems.disks.r2.bucket', 'intan-elyu-media');
        $result = $client->listMultipartUploads([
            'Bucket' => $bucket,
            'Prefix' => $targetKey,
        ]);
        $uploads = $result['Uploads'] ?? [];
        $aborted = 0;
        foreach ($uploads as $upload) {
            if (!empty($upload['Key']) && !empty($upload['UploadId'])) {
                $client->abortMultipartUpload([
                    'Bucket' => $bucket,
                    'Key' => $upload['Key'],
                    'UploadId' => $upload['UploadId'],
                ]);
                $aborted++;
                $this->line("🧹 Aborted incomplete multipart upload: {$upload['Key']} (UploadId: " . substr($upload['UploadId'], 0, 12) . "...)");
            }
        }
        return $aborted;
    }
}
