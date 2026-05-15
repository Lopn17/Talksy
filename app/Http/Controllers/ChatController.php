<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Message;

class ChatController extends Controller
{
    public function index()
    {
        $messages = Message::withTrashed()
            ->with('user')
            ->oldest()
            ->get();

        return view('chat', compact('messages'));
    }

public function poll(Request $request)
{
    $since = $request->query('since') 
        ? \Carbon\Carbon::parse($request->query('since'))->setTimezone('Asia/Jakarta') 
        : null;

    $newMessages = Message::with('user')
        ->when($since, fn($q) => $q->where('created_at', '>', $since))
        ->oldest()
        ->take(50)
        ->get();

    $updatedMessages = collect();
    if ($since) {
        $updatedMessages = Message::withTrashed()
            ->with('user')
            ->where('updated_at', '>', $since)
            ->where('created_at', '<=', $since)
            ->get();
    }

    return response()->json([
        'new'     => $newMessages,
        'updated' => $updatedMessages,
    ]);
}

    public function send(Request $request)
    {
        $request->validate(['message' => 'required|string|max:2000']);

        $message = Message::create([
            'user_id' => auth()->id(),
            'message' => $request->message,
        ]);

        $message->load('user');

        return response()->json($message);
    }

    public function delete($id)
    {
        $message = Message::findOrFail($id);
        abort_if($message->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $message->delete();

        if (request()->wantsJson()) {
            return response()->json(['deleted' => true]);
        }
        return redirect('/chat');
    }


    public function undoDelete($id)
    {
        $message = Message::withTrashed()->findOrFail($id);
        abort_if($message->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        $message->restore();

        if (request()->wantsJson()) {
            return response()->json(['restored' => true]);
        }
        return redirect('/chat');
    }

        public function edit(Request $request, $id)
        {
            $request->validate(['message' => 'required|string|max:2000']);

            $message = Message::findOrFail($id);
            abort_if($message->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);

            $message->update([
                'message'   => $request->message,
                'is_edited' => true,
                'edited_at' => now(),
            ]);

            $message->load('user');

            if (request()->wantsJson()) {
                return response()->json($message);
            }
            return redirect('/chat');
        }

    public function typing(Request $request)
    {
        // Update typing_at timestamp for current user
        auth()->user()->update(['typing_at' => now()]);
        return response()->json(['ok' => true]);
    }

    public function whoTyping()
    {
        // Anyone who updated typing_at in the last 3 seconds, excluding me
        $typers = \App\Models\User::where('id', '!=', auth()->id())
            ->whereNotNull('typing_at')
            ->where('typing_at', '>=', now()->subSeconds(3))
            ->pluck('name');

        return response()->json(['typers' => $typers]);
    }

    public function getOne($id)
    {
        $message = Message::withTrashed()->with('user')->findOrFail($id);
        abort_if($message->user_id !== auth()->id() && !auth()->user()->isAdmin(), 403);
        return response()->json($message);
    }
    
}