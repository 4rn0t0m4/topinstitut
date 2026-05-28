<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Establishment;
use App\Models\Photo;
use App\Services\PhotoUploadService;
use Illuminate\Http\Request;

class PhotoController extends Controller
{
    public function __construct(private PhotoUploadService $photos) {}

    public function index(Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        return view('client.photos.index', [
            'etablissement' => $etablissement,
            'photos' => $etablissement->photos,
        ]);
    }

    public function store(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $request->validate(['photo' => 'required|image|max:5120']);

        $photo = $this->photos->upload($etablissement, $request->file('photo'));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'id' => $photo->id, 'url' => $photo->url]);
        }

        return back()->with('success', 'Photo ajoutée.');
    }

    public function destroy(Establishment $etablissement, Photo $photo)
    {
        $this->authorize('manage', $etablissement);

        $this->photos->delete($photo);

        return back()->with('success', 'Photo supprimée.');
    }

    public function reorder(Request $request, Establishment $etablissement)
    {
        $this->authorize('manage', $etablissement);

        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:photos,id']);

        $this->photos->reorder($etablissement, $request->order);

        return response()->json(['ok' => true]);
    }
}
