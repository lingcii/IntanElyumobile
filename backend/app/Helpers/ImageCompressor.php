<?php

namespace App\Helpers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\File;

class ImageCompressor
{
    /**
     * Compress and store an uploaded image file (Auto-resize to max $maxWidth px & convert to WebP / compressed JPEG).
     *
     * @param UploadedFile $file
     * @param string $folder
     * @param string $disk
     * @param string $prefix
     * @param int $maxWidth
     * @param int $quality
     * @return string Stored relative path
     */
    public static function compressAndStore(
        UploadedFile $file,
        string $folder = 'tourist_spots',
        string $disk = 'public',
        string $prefix = 'spot_',
        int $maxWidth = 1200,
        int $quality = 80
    ): string {
        try {
            $tempPath = $file->getRealPath();
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'jpg');

            $image = null;
            switch ($extension) {
                case 'jpeg':
                case 'jpg':
                    if (function_exists('imagecreatefromjpeg')) {
                        $image = @imagecreatefromjpeg($tempPath);
                    }
                    break;
                case 'png':
                    if (function_exists('imagecreatefrompng')) {
                        $image = @imagecreatefrompng($tempPath);
                    }
                    break;
                case 'webp':
                    if (function_exists('imagecreatefromwebp')) {
                        $image = @imagecreatefromwebp($tempPath);
                    }
                    break;
            }

            if ($image) {
                $width = imagesx($image);
                $height = imagesy($image);

                // Resize down if wider than $maxWidth
                if ($width > $maxWidth) {
                    $newWidth = $maxWidth;
                    $newHeight = (int) round($height * ($maxWidth / $width));
                    $resized = imagecreatetruecolor($newWidth, $newHeight);

                    // Preserve alpha transparency for PNG/WebP
                    imagealphablending($resized, false);
                    imagesavealpha($resized, true);

                    imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                    imagedestroy($image);
                    $image = $resized;
                }

                // Prefer WebP format if supported by PHP GD, otherwise fallback to JPEG
                if (function_exists('imagewebp')) {
                    $filename = $prefix . uniqid() . '.webp';
                    $savePath = sys_get_temp_dir() . '/' . $filename;
                    imagewebp($image, $savePath, $quality);
                } else {
                    $filename = $prefix . uniqid() . '.jpg';
                    $savePath = sys_get_temp_dir() . '/' . $filename;
                    imagejpeg($image, $savePath, $quality);
                }
                imagedestroy($image);

                // Upload compressed file to Cloudflare R2 / storage disk
                $storedPath = Storage::disk($disk)->putFileAs($folder, new File($savePath), $filename);
                @unlink($savePath);

                if ($storedPath) {
                    return $storedPath;
                }
            }
        } catch (\Throwable $e) {
            // Fallback to standard Laravel store if GD compression encounters any exception
        }

        return $file->store($folder, $disk);
    }
}
