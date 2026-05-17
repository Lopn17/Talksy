<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AnimalChessController extends Controller
{
    private const STATE_KEY   = 'animal_chess_state';
    private const TTL         = 86400; // 24 jam

    /**
     * Daftar hewan: id, rank, emoji, name
     */
    private const ANIMALS = [
        ['id' => 'elephant', 'name' => 'Gajah',    'emoji' => '🐘', 'rank' => 8],
        ['id' => 'lion',     'name' => 'Singa',    'emoji' => '🦁', 'rank' => 7],
        ['id' => 'tiger',    'name' => 'Harimau',  'emoji' => '🐯', 'rank' => 6],
        ['id' => 'panther',  'name' => 'Panther',  'emoji' => '🐆', 'rank' => 5],
        ['id' => 'wolf',     'name' => 'Serigala', 'emoji' => '🐺', 'rank' => 4],
        ['id' => 'fox',      'name' => 'Rubah',    'emoji' => '🦊', 'rank' => 3],
        ['id' => 'cat',      'name' => 'Kucing',   'emoji' => '🐱', 'rank' => 2],
        ['id' => 'mouse',    'name' => 'Tikus',    'emoji' => '🐭', 'rank' => 1],
    ];

    // ── Helpers ───────────────────────────────────────────────────

    /**
     * Generate state awal: board 4x4 berisi 16 piece (8 P1 + 8 P2) diacak
     */
    private function freshState(): array
    {
        $pieces = [];
        foreach (self::ANIMALS as $animal) {
            foreach ([1, 2] as $owner) {
                $pieces[] = [
                    'id'       => $animal['id'],
                    'name'     => $animal['name'],
                    'emoji'    => $animal['emoji'],
                    'rank'     => $animal['rank'],
                    'owner'    => $owner,
                    'revealed' => false,
                ];
            }
        }

        // Fisher-Yates shuffle
        $n = count($pieces); // 16
        for ($i = $n - 1; $i > 0; $i--) {
            $j = random_int(0, $i);
            [$pieces[$i], $pieces[$j]] = [$pieces[$j], $pieces[$i]];
        }

        // Susun jadi board 4×4 (array of rows, each row = 4 cells, cell = piece|null)
        $board = [];
        $idx   = 0;
        for ($r = 0; $r < 4; $r++) {
            $row = [];
            for ($c = 0; $c < 4; $c++) {
                $row[] = $pieces[$idx++];
            }
            $board[] = $row;
        }

        return [
            'board'          => $board,
            'current_player' => 1,
            'scores'         => [1 => 0, 2 => 0],
            'captured'       => [1 => [], 2 => []],
            'move_count'     => 0,
            'game_over'      => false,
            'winner'         => null,          // 1 | 2 | 'draw' | null
            'over_reason'    => null,          // 'max_moves' | 'pieces_gone'
            'updated_at'     => now()->timestamp,
            'last_mover'     => null,          // username
        ];
    }

    private function getState(): array
    {
        return Cache::get(self::STATE_KEY) ?? $this->freshState();
    }

    private function saveState(array $state): void
    {
        $state['updated_at'] = now()->timestamp;
        Cache::put(self::STATE_KEY, $state, self::TTL);
    }

    // ── Routes ────────────────────────────────────────────────────

    public function index()
    {
        return view('animal-chess');
    }

    /** GET /games/animal-chess/state */
    public function state()
    {
        return response()->json($this->getState());
    }

    /** POST /games/animal-chess/move */
    public function move(Request $request)
    {
        $request->validate([
            'action'        => 'required|string|in:reveal,move',
            'from_r'        => 'required|integer|between:0,3',
            'from_c'        => 'required|integer|between:0,3',
            // to_r / to_c hanya wajib kalau action = 'move'
            'to_r'          => 'nullable|integer|between:0,3',
            'to_c'          => 'nullable|integer|between:0,3',
        ]);

        $state = $this->getState();

        // Tolak kalau sudah game over
        if ($state['game_over']) {
            return response()->json(['ok' => false, 'error' => 'Game sudah selesai.'], 422);
        }

        $player = $state['current_player'];
        $fr     = $request->integer('from_r');
        $fc     = $request->integer('from_c');
        $action = $request->input('action');

        $cell = $state['board'][$fr][$fc] ?? null;
        if (!$cell) {
            return response()->json(['ok' => false, 'error' => 'Sel kosong.'], 422);
        }

        // ── REVEAL ──────────────────────────────────────────────
        if ($action === 'reveal') {
            if ($cell['revealed']) {
                return response()->json(['ok' => false, 'error' => 'Piece sudah terbuka.'], 422);
            }
            $state['board'][$fr][$fc]['revealed'] = true;
            $state['move_count']++;
            $state['current_player'] = $player === 1 ? 2 : 1;
            $state['last_mover']     = auth()->user()->name ?? 'Unknown';
            $this->checkGameOver($state);
            $this->saveState($state);
            return response()->json(['ok' => true, 'state' => $state]);
        }

        // ── MOVE ────────────────────────────────────────────────
        $tr = $request->integer('to_r');
        $tc = $request->integer('to_c');

        if (!$cell['revealed'] || $cell['owner'] !== $player) {
            return response()->json(['ok' => false, 'error' => 'Bukan piece kamu.'], 422);
        }

        // Validasi adjacency
        $dr = abs($tr - $fr);
        $dc = abs($tc - $fc);
        if (!(($dr === 1 && $dc === 0) || ($dr === 0 && $dc === 1))) {
            return response()->json(['ok' => false, 'error' => 'Gerakan tidak valid.'], 422);
        }

        $target = $state['board'][$tr][$tc];

        if ($target === null) {
            // Pindah ke sel kosong
            $state['board'][$tr][$tc] = $cell;
            $state['board'][$fr][$fc] = null;
        } else if ($target['owner'] === $player) {
            return response()->json(['ok' => false, 'error' => 'Tidak bisa ke sel milik sendiri.'], 422);
        } else {
            // Musuh — cek apakah terbuka atau tidak
            if (!$target['revealed']) {
                // Buka paksa dulu, lalu terapkan aturan capture
                $target['revealed'] = true;
            }

            // Aturan capture
            $aCapturesD = $this->canCapture($cell, $target);
            $dCapturesA = $this->canCapture($target, $cell);

            if ($cell['rank'] === $target['rank']) {
                // Seri — keduanya hilang
                $state['captured'][$player][]                    = $cell;
                $state['captured'][$target['owner']][]           = $target;
                $state['board'][$fr][$fc]                        = null;
                $state['board'][$tr][$tc]                        = null;
            } elseif ($aCapturesD) {
                // Penyerang menang
                $state['scores'][$player]                       += $target['rank'];
                $state['captured'][$player][]                    = $target;
                $state['board'][$tr][$tc]                        = $cell;
                $state['board'][$fr][$fc]                        = null;
            } else {
                // Bertahan menang
                $state['scores'][$target['owner']]              += $cell['rank'];
                $state['captured'][$target['owner']][]           = $cell;
                $state['board'][$fr][$fc]                        = null;
                // Target tetap di tempatnya (tapi kini revealed)
                $state['board'][$tr][$tc]['revealed']            = true;
            }
        }

        $state['move_count']++;
        $state['current_player'] = $player === 1 ? 2 : 1;
        $state['last_mover']     = auth()->user()->name ?? 'Unknown';
        $this->checkGameOver($state);
        $this->saveState($state);

        return response()->json(['ok' => true, 'state' => $state]);
    }

    /** POST /games/animal-chess/reset */
    public function reset()
    {
        $fresh = $this->freshState();
        $this->saveState($fresh);
        return response()->json(['ok' => true, 'state' => $fresh]);
    }

    // ── Private helpers ───────────────────────────────────────────

    private function canCapture(array $attacker, array $defender): bool
    {
        // Tikus (rank 1) kalahkan Gajah (rank 8)
        if ($attacker['rank'] === 1 && $defender['rank'] === 8) return true;
        // Gajah tidak bisa kalahkan Tikus
        if ($attacker['rank'] === 8 && $defender['rank'] === 1) return false;
        return $attacker['rank'] >= $defender['rank'];
    }

    private function checkGameOver(array &$state): void
    {
        // Kondisi A: max 50 langkah
        if ($state['move_count'] >= 50) {
            $state['game_over']  = true;
            $state['over_reason'] = 'max_moves';
            $state['winner']     = $this->determineWinner($state['scores']);
            return;
        }

        // Kondisi B: salah satu atau keduanya habis
        $p1 = 0;
        $p2 = 0;
        foreach ($state['board'] as $row) {
            foreach ($row as $cell) {
                if ($cell !== null) {
                    if ($cell['owner'] === 1) $p1++;
                    else $p2++;
                }
            }
        }

        if ($p1 === 0 || $p2 === 0) {
            $state['game_over']  = true;
            $state['over_reason'] = 'pieces_gone';
            $state['winner']     = $this->determineWinner($state['scores']);
        }
    }

    private function determineWinner(array $scores): string
    {
        if ($scores[1] > $scores[2]) return '1';
        if ($scores[2] > $scores[1]) return '2';
        return 'draw';
    }
}