<?php

namespace App\Services;

use App\Models\Establishment;
use App\Models\Photo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PhotoUploadService
{
    /**
     * Upload a photo to R2 and persist the Photo record.
     */
    public function upload(Establishment $establishment, UploadedFile $file): Photo
    {
        $filename = uniqid('', true).'.'.$file->getClientOriginalExtension();
        $path = "etablissements/{$establishment->id}/{$filename}";

        Storage::disk('r2')->put($path, $file->get(), [
            'ContentType' => $file->getMimeType(),
        ]);

        return $establishment->photos()->create([
            'filename' => $filename,
            'sort_order' => $establishment->photos()->count(),
        ]);
    }

    /**
     * Delete the Photo record and its R2 object.
     */
    public function delete(Photo $photo): void
    {
        $path = "etablissements/{$photo->establishment_id}/{$photo->filename}";
        Storage::disk('r2')->delete($path);
        $photo->delete();
    }

    /**
     * Reorder photos by passing an ordered list of IDs.
     *
     * @param  array<int>  $ids
     */
    public function reorder(Establishment $establishment, array $ids): void
    {
        foreach ($ids as $index => $id) {
            Photo::where('id', $id)
                ->where('establishment_id', $establishment->id)
                ->update(['sort_order' => $index]);
        }
    }
}
