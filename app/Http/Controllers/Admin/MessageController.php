<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = Message::with('establishment:id,name,slug,type,city_id')
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $term = '%'.$request->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('email', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('content', 'like', $term);
            });
        }

        $messages = $query->paginate(25)->withQueryString();

        return view('admin.messages.index', compact('messages'));
    }

    public function show(Message $message)
    {
        $message->load('establishment');

        return view('admin.messages.show', compact('message'));
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message supprimé.');
    }
}
