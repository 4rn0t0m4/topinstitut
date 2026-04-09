<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Etablissement;
use App\Models\Photo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PhotoController extends Controller
{
    private function authorize(Request $request, Etablissement $etablissement): void
    {
        if (! $request->user()->etablissements()->where('etablissement_id', $etablissement->id)->exists()) {
            abort(403);
        }
    }

    public function index(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);
        $photos = $etablissement->photos;

        return view('client.photos.index', compact('etablissement', 'photos'));
    }

    public function store(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        $request->validate([
            'photo' => 'required|image|max:5120',
        ]);

        $path = $request->file('photo')->store("etablissements/{$etablissement->id}", 'public');

        $etablissement->photos()->create([
            'filename' => $path,
            'ordre' => $etablissement->photos()->count(),
        ]);

        return back()->with('success', 'Photo ajoutée.');
    }

    public function destroy(Request $request, Etablissement $etablissement, Photo $photo)
    {
        $this->authorize($request, $etablissement);

        Storage::disk('public')->delete($photo->filename);
        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    public function reorder(Request $request, Etablissement $etablissement)
    {
        $this->authorize($request, $etablissement);

        $request->validate(['order' => 'required|array', 'order.*' => 'integer|exists:photos,id']);

        foreach ($request->order as $index => $id) {
            Photo::where('id', $id)->where('etablissement_id', $etablissement->id)->update(['ordre' => $index]);
        }

        return response()->json(['ok' => true]);
    }
}
