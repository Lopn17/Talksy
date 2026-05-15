<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class RpsController extends Controller
{
    private const KEY = 'rps_game';

    public function index()
    {
        return view('rps');
    }

    // ── Poll ─────────────────────────────────────────────
    public function state(Request $request)
    {
        $myId = session()->getId();
        $game = Cache::get(self::KEY, $this->emptyGame());

        // Build player list — hide other players' choices unless revealed
        $players = [];
        foreach ($game['players'] as $id => $p) {
            $players[] = [
                'id'     => $id,
                'name'   => $p['name'],
                'ready'  => $p['choice'] !== null,
                'choice' => ($game['revealed'] || $id === $myId) ? $p['choice'] : null,
                'isMe'   => $id === $myId,
                'result' => $game['revealed'] ? ($p['result'] ?? null) : null,
            ];
        }

        return response()->json([
            'status'   => $game['revealed'] ? 'revealed' : 'picking',
            'players'  => $players,
            'revealed' => $game['revealed'],
            'allReady' => $this->allReady($game),
        ]);
    }

    // ── Join / set name ───────────────────────────────────
    public function join(Request $request)
    {
        $request->validate(['name' => 'required|string|max:20']);
        $myId = session()->getId();
        $game = Cache::get(self::KEY, $this->emptyGame());

        // Add or update player
        if (!isset($game['players'][$myId])) {
            $game['players'][$myId] = ['name' => $request->name, 'choice' => null, 'result' => null];
        } else {
            $game['players'][$myId]['name'] = $request->name;
        }

        Cache::put(self::KEY, $game);
        return response()->json(['status' => 'joined']);
    }

    // ── Pick ──────────────────────────────────────────────
    public function pick(Request $request)
    {
        $request->validate(['choice' => 'required|in:rock,paper,scissors']);
        $myId = session()->getId();
        $game = Cache::get(self::KEY, $this->emptyGame());

        if ($game['revealed']) {
            return response()->json(['error' => 'Already revealed'], 422);
        }

        if (!isset($game['players'][$myId])) {
            return response()->json(['error' => 'Join first'], 422);
        }

        $game['players'][$myId]['choice'] = $request->choice;
        Cache::put(self::KEY, $game);

        return response()->json(['status' => 'picked']);
    }

    // ── Clash (reveal) ────────────────────────────────────
    public function clash()
    {
        $game = Cache::get(self::KEY, $this->emptyGame());

        if (!$this->allReady($game) || count($game['players']) < 2) {
            return response()->json(['error' => 'Not everyone is ready'], 422);
        }

        $game['revealed'] = true;
        $game['players']  = $this->resolveResults($game['players']);
        Cache::put(self::KEY, $game);

        return response()->json(['status' => 'revealed']);
    }

    // ── Reset ─────────────────────────────────────────────
    public function reset()
    {
        // Keep players but clear choices
        $game = Cache::get(self::KEY, $this->emptyGame());
        foreach ($game['players'] as $id => $p) {
            $game['players'][$id]['choice'] = null;
            $game['players'][$id]['result'] = null;
        }
        $game['revealed'] = false;
        Cache::put(self::KEY, $game);

        return response()->json(['status' => 'reset']);
    }

    // ── Leave ─────────────────────────────────────────────
    public function leave()
    {
        $myId = session()->getId();
        $game = Cache::get(self::KEY, $this->emptyGame());
        unset($game['players'][$myId]);
        Cache::put(self::KEY, $game);

        return response()->json(['status' => 'left']);
    }

    // ── Helpers ───────────────────────────────────────────
    private function emptyGame(): array
    {
        return ['players' => [], 'revealed' => false];
    }

    private function allReady(array $game): bool
    {
        if (empty($game['players'])) return false;
        foreach ($game['players'] as $p) {
            if ($p['choice'] === null) return false;
        }
        return true;
    }

    private function resolveResults(array $players): array
    {
        $beats = ['rock' => 'scissors', 'scissors' => 'paper', 'paper' => 'rock'];
        $ids   = array_keys($players);

        // Collect all unique choices
        $choices = array_unique(array_column(array_values($players), 'choice'));

        // If all same → tie
        if (count($choices) === 1) {
            foreach ($ids as $id) $players[$id]['result'] = 'tie';
            return $players;
        }

        // If all 3 present → three-way tie
        if (count($choices) === 3) {
            foreach ($ids as $id) $players[$id]['result'] = 'tie';
            return $players;
        }

        // Otherwise: find winning choice
        foreach ($ids as $id) {
            $myChoice   = $players[$id]['choice'];
            $isWinner   = true;
            foreach ($ids as $otherId) {
                if ($otherId === $id) continue;
                $theirChoice = $players[$otherId]['choice'];
                if ($beats[$myChoice] !== $theirChoice) { $isWinner = false; break; }
            }
            $players[$id]['result'] = $isWinner ? 'win' : 'lose';
        }

        return $players;
    }
}