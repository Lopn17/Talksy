<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class TictactoeController extends Controller
{
    private function defaultState(int $size = 3): array
    {
        return [
            'board'      => array_fill(0, $size * $size, null),
            'pieces_x'   => [],
            'pieces_o'   => [],
            'board_size' => $size,
            'turn'       => 'X',   // ← tambah ini
            'status'     => 'playing',
            'version'    => 0,
        ];
    }

    private function getState(): array
    {
        return Cache::get('tictactoe', $this->defaultState());
    }

    private function checkWin(array $board, string $player, int $size): ?array
    {
        for ($r = 0; $r < $size; $r++) {
            $combo = [];
            $win = true;
            for ($c = 0; $c < $size; $c++) {
                $idx = $r * $size + $c;
                $combo[] = $idx;
                if ($board[$idx] !== $player) $win = false;
            }
            if ($win) return $combo;
        }
        for ($c = 0; $c < $size; $c++) {
            $combo = [];
            $win = true;
            for ($r = 0; $r < $size; $r++) {
                $idx = $r * $size + $c;
                $combo[] = $idx;
                if ($board[$idx] !== $player) $win = false;
            }
            if ($win) return $combo;
        }
        $combo = [];
        $win = true;
        for ($i = 0; $i < $size; $i++) {
            $idx = $i * $size + $i;
            $combo[] = $idx;
            if ($board[$idx] !== $player) $win = false;
        }
        if ($win) return $combo;

        $combo = [];
        $win = true;
        for ($i = 0; $i < $size; $i++) {
            $idx = $i * $size + ($size - 1 - $i);
            $combo[] = $idx;
            if ($board[$idx] !== $player) $win = false;
        }
        if ($win) return $combo;

        return null;
    }

    public function index()
    {
        return view('tictactoe');
    }

    public function state()
    {
        return response()->json($this->getState());
    }

    public function move(Request $request)
    {
        $request->validate(['index' => 'required|integer|min:0']);

        $game  = $this->getState();
        $index = $request->index;
        $size  = $game['board_size'];
        $board = $game['board'];

        if ($game['status'] !== 'playing') {
            return response()->json(['error' => 'Game over'], 422);
        }

        if ($index >= count($board)) {
            return response()->json(['error' => 'Invalid cell'], 422);
        }

        // GANTI jadi:
        $current = $board[$index];
        if ($current !== null) {
            return response()->json(['error' => 'Cell already taken'], 422);
        }
        $next = $game['turn'];  // pakai giliran saat ini

        // Remove from old pieces list if it was occupied
        if ($current !== null) {
            $key = 'pieces_' . strtolower($current);
            $game[$key] = array_values(array_filter($game[$key], fn($i) => $i !== $index));
        }

        $board[$index] = $next;

        // Add to new pieces list if now occupied
        if ($next !== null) {
            $key = 'pieces_' . strtolower($next);
            $game[$key][] = $index;

            // Remove oldest if over limit
            $limits = [3 => 4, 4 => 7, 5 => 10, 6 => 13];

            $limit  = $limits[$size] ?? $size + 1;

            if (count($game[$key]) > $limit) {
                $oldest = array_shift($game[$key]);
                $board[$oldest] = null;
            }

            // Check win
            $winCombo = $this->checkWin($board, $next, $size);
            if ($winCombo) {
                $game['status'] = 'won_' . strtolower($next);
            } elseif (!in_array(null, $board)) {
                $game['status'] = 'draw';
            }
        }

        $game['board']   = $board;
        $game['turn']    = ($next === 'X') ? 'O' : 'X';  // ← tambah ini
        $game['version'] = ($game['version'] ?? 0) + 1;

        Cache::put('tictactoe', $game, now()->addDays(7));

        return response()->json($game);
    }

    public function reset(Request $request)
    {
        $size  = (int) $request->input('size', $this->getState()['board_size']);
        $size  = in_array($size, [3, 4, 5, 6]) ? $size : 3;
        $fresh = $this->defaultState($size);
        Cache::put('tictactoe', $fresh, now()->addDays(7));
        return response()->json($fresh);
    }
}