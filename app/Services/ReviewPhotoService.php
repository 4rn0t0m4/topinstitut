<?php

namespace App\Services;

use App\Models\Review;
use App\Models\ReviewPhoto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ReviewPhotoService
{
    /**
     * Upload, resize to WebP (max 1200px), store on R2, and create ReviewPhoto records.
     * Silently skips if GD/WebP is unavailable.
     */
    public function upload(Review $review, array $files): void
    {
        if (!function_exists('imagewebp')) {
            return;
        }

        foreach (array_values($files) as $i => $file) {
            try {
                $this->processSinglePhoto($review, $file, $i);
            } catch (\Throwable $e) {
                Log::warning('Review photo upload failed: ' . $e->getMessage());
            }
        }
    }

    private function processSinglePhoto(Review $review, UploadedFile $file, int $index): void
    {
        $image = @imagecreatefromstring(file_get_contents($file->getRealPath()));
        if (!$image) {
            return;
        }

        [$width, $height] = $this->resizeIfNeeded($image, 1200);

        ob_start();
        imagewebp($image, null, 80);
        $bytes = ob_get_clean();
        imagedestroy($image);

        if (!$bytes) {
            return;
        }

        $this->storeOnR2($review, $bytes, $index);
    }

    private function resizeIfNeeded($image, int $maxWidth): array
    {
        $w = imagesx($image);
        $h = imagesy($image);

        if ($w <= $maxWidth) {
            return [$w, $h];
        }

        $newH = (int)round($h * ($maxWidth / $w));
        $resized = imagecreatetruecolor($maxWidth, $newH);
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $maxWidth, $newH, $w, $h);
        imagedestroy($image);

        return [$maxWidth, $newH];
    }

    private function storeOnR2(Review $review, string $bytes, int $index): void
    {
        $filename = 'review_' . ($index + 1) . '.webp';
        $path = "reviews/{$review->id}/{$filename}";

        Storage::disk('r2')->put($path, $bytes, [
            'ContentType' => 'image/webp',
            'CacheControl' => 'public, max-age=15552000, immutable',
        ]);

        ReviewPhoto::create([
            'review_id' => $review->id,
            'filename' => $filename,
            'sort_order' => $index,
        ]);
    }

    public function delete(ReviewPhoto $photo): void
    {
        $path = "reviews/{$photo->review_id}/{$photo->filename}";
        Storage::disk('r2')->delete($path);
        $photo->delete();
    }
}
