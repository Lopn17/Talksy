<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ChessController extends Controller
{
    private const CACHE_KEY   = 'chess_board_state';
    private const HISTORY_KEY = 'chess_move_history';
    private const START_FEN   = 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1';
    private const TTL         = 86400;

    public function index()
    {
        return view('chess');
    }

    /** GET /games/chess/state */
    public function state()
    {
        $state = Cache::get(self::CACHE_KEY, [
            'fen'        => self::START_FEN,
            'turn'       => 'w',
            'status'     => 'playing',
            'winner'     => null,
            'updated_at' => now()->timestamp,
        ]);

        $history = Cache::get(self::HISTORY_KEY, []);

        return response()->json([
            'fen'        => $state['fen'],
            'turn'       => $state['turn'],
            'status'     => $state['status'],
            'winner'     => $state['winner'] ?? null,
            'updated_at' => $state['updated_at'],
            'history'    => $history,
        ]);
    }

    /** POST /games/chess/move */
    public function move(Request $request)
    {
        $request->validate([
            'fen'    => 'required|string',
            'turn'   => 'required|string|in:w,b',
            'san'    => 'required|string',
            'status' => 'required|string',
        ]);

        $state = [
            'fen'        => $request->fen,
            'turn'       => $request->turn,
            'status'     => $request->status,
            'winner'     => $request->input('winner'),   // 'w', 'b', or null
            'updated_at' => now()->timestamp,
        ];

        Cache::put(self::CACHE_KEY, $state, self::TTL);

        $history   = Cache::get(self::HISTORY_KEY, []);
        $history[] = [
            'san'      => $request->san,
            'player'   => $request->input('player', 'Unknown'),
            'moved_at' => now()->timestamp,
        ];
        Cache::put(self::HISTORY_KEY, $history, self::TTL);

        return response()->json(['ok' => true, 'updated_at' => $state['updated_at']]);
    }

    /** POST /games/chess/reset */
    public function reset()
    {
        Cache::put(self::CACHE_KEY, [
            'fen'        => self::START_FEN,
            'turn'       => 'w',
            'status'     => 'playing',
            'winner'     => null,
            'updated_at' => now()->timestamp,
        ], self::TTL);

        Cache::put(self::HISTORY_KEY, [], self::TTL);

        return response()->json(['ok' => true]);
    }
}