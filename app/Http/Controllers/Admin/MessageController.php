<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    public function index(Request $request)
    {
        $query = Message::with('establishment:id,name,slug,type,city_id')
            ->latest();

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->whereNull('handled_at');
            } elseif ($request->status === 'handled') {
                $query->whereNotNull('handled_at');
            }
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

        $counts = [
            'all' => Message::count(),
            'pending' => Message::whereNull('handled_at')->count(),
        ];

        return view('admin.messages.index', compact('messages', 'counts'));
    }

    public function show(Message $message)
    {
        $message->load('establishment');

        $otherFromSender = Message::where('id', '!=', $message->id)
            ->where('email', $message->email)
            ->latest()
            ->limit(10)
            ->get();

        return view('admin.messages.show', compact('message', 'otherFromSender'));
    }

    public function destroy(Message $message)
    {
        $message->delete();

        return redirect()->route('admin.messages.index')->with('success', 'Message supprimé.');
    }

    public function toggleHandled(Message $message)
    {
        $message->update([
            'handled_at' => $message->handled_at ? null : now(),
        ]);

        return back()->with('success', $message->handled_at ? 'Marqué comme traité.' : 'Marqué comme non traité.');
    }

    public function forward(Message $message)
    {
        if (! $message->establishment || ! $message->establishment->email) {
            return back()->withErrors(['forward' => 'L\'établissement n\'a pas d\'email renseigné.']);
        }

        $body = "Message reçu sur TopInstitut :\n\n"
            ."De : {$message->name} <{$message->email}>"
            .($message->phone ? " — tél: {$message->phone}" : '')
            ."\nReçu le : {$message->created_at->format('d/m/Y à H:i')}\n";

        if ($message->type === 'booking') {
            $body .= "Type : demande de RDV\n";
            if ($message->requested_date) $body .= "Date souhaitée : {$message->requested_date->format('d/m/Y')}\n";
            if ($message->requested_time) $body .= "Heure : {$message->requested_time}\n";
            if ($message->requested_service) $body .= "Prestation : {$message->requested_service}\n";
        }

        $body .= "\n---\n".$message->content."\n";

        Mail::raw($body, function ($mail) use ($message) {
            $mail->to($message->establishment->email)
                ->replyTo($message->email, $message->name ?: $message->email)
                ->subject('[TopInstitut] Message à transférer — '.($message->name ?: $message->email));
        });

        $message->update(['handled_at' => now()]);

        return back()->with('success', 'Message transféré à '.$message->establishment->email.' (et marqué comme traité).');
    }
}
