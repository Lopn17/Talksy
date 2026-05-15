<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SudokuController extends Controller
{
    private const KEY     = 'sudoku_game';
    private const LEVELS  = [
        'Easy'      => 29,
        'Medium'    => 38,
        'Hard'      => 47,
        'Very Hard'  => 56,
        'Insane'    => 65,
        'Inhuman'   => 74,
    ];

    // ── Page ─────────────────────────────────────────────
    public function index()
    {
        return view('sudoku');
    }

    // ── Poll ─────────────────────────────────────────────
    public function state()
    {
        $game = Cache::get(self::KEY);

        if (!$game) {
            return response()->json(['status' => 'waiting']);
        }

        // Tick timer server-side
        if (!$game['paused'] && !$game['completed']) {
            $game['seconds'] = time() - $game['started_at'];
            Cache::put(self::KEY, $game);
        }

        return response()->json([
            'status'    => $game['completed'] ? 'completed' : 'active',
            'puzzle'    => $game['puzzle'],
            'board'     => $game['board'],
            'level'     => $game['level'],
            'seconds'   => $game['seconds'],
            'paused'    => $game['paused'],
            'completed' => $game['completed'],
        ]);
    }

    // ── Start ─────────────────────────────────────────────
    public function start(Request $request)
    {
        $request->validate([
            'level' => 'required|in:Easy,Medium,Hard,Very Hard,Insane,Inhuman'
        ]);

        [$puzzle, $solution] = $this->generateSudoku($request->level);

        Cache::put(self::KEY, [
            'puzzle'     => $puzzle,
            'board'      => $puzzle,
            'solution'   => $solution,
            'level'      => $request->level,
            'seconds'    => 0,
            'paused'     => false,
            'completed'  => false,
            'started_at' => time(),
        ]);

        return response()->json(['status' => 'started']);
    }

    // ── Move ─────────────────────────────────────────────
    public function move(Request $request)
    {
        $request->validate([
            'row'   => 'required|integer|min:0|max:8',
            'col'   => 'required|integer|min:0|max:8',
            'value' => 'required|integer|min:0|max:9',
        ]);

        $game = Cache::get(self::KEY);

        if (!$game || $game['completed'] || $game['paused']) {
            return response()->json(['error' => 'Game not active'], 422);
        }

        // Protect clue cells
        if ($game['puzzle'][$request->row][$request->col] !== 0) {
            return response()->json(['error' => 'Cannot overwrite clue'], 422);
        }

        $game['board'][$request->row][$request->col] = $request->value;

        if ($this->isSolved($game['board'], $game['solution'])) {
            $game['completed'] = true;
            $game['seconds']   = time() - $game['started_at'];
        }

        Cache::put(self::KEY, $game);

        return response()->json([
            'status'    => $game['completed'] ? 'completed' : 'active',
            'board'     => $game['board'],
            'completed' => $game['completed'],
            'seconds'   => $game['seconds'],
        ]);
    }

    // ── Pause / Resume ───────────────────────────────────
    public function pause(Request $request)
    {
        $game = Cache::get(self::KEY);
        if (!$game) return response()->json(['error' => 'No game'], 404);

        if ($request->boolean('paused')) {
            $game['seconds']    = time() - $game['started_at'];
            $game['paused']     = true;
            $game['started_at'] = null;
        } else {
            $game['paused']     = false;
            $game['started_at'] = time() - $game['seconds'];
        }

        Cache::put(self::KEY, $game);
        return response()->json(['paused' => $game['paused']]);
    }

    // ── Reset ─────────────────────────────────────────────
    public function reset()
    {
        Cache::forget(self::KEY);
        return response()->json(['status' => 'reset']);
    }

    // ══════════════════════════════════════════════════════
    // Sudoku generation
    // ══════════════════════════════════════════════════════

    private function generateSudoku(string $level): array
    {
        $grid = array_fill(0, 9, array_fill(0, 9, 0));
        $this->fillGrid($grid);
        $solution = $grid;
        $puzzle   = $this->removeCells($grid, self::LEVELS[$level]);
        return [$puzzle, $solution];
    }

    private function fillGrid(array &$grid): bool
    {
        for ($row = 0; $row < 9; $row++) {
            for ($col = 0; $col < 9; $col++) {
                if ($grid[$row][$col] === 0) {
                    $nums = range(1, 9);
                    shuffle($nums);
                    foreach ($nums as $num) {
                        if ($this->isSafe($grid, $row, $col, $num)) {
                            $grid[$row][$col] = $num;
                            if ($this->fillGrid($grid)) return true;
                            $grid[$row][$col] = 0;
                        }
                    }
                    return false;
                }
            }
        }
        return true;
    }

    private function isSafe(array $grid, int $row, int $col, int $num): bool
    {
        if (in_array($num, $grid[$row])) return false;
        for ($r = 0; $r < 9; $r++) {
            if ($grid[$r][$col] === $num) return false;
        }
        $br = $row - $row % 3;
        $bc = $col - $col % 3;
        for ($r = 0; $r < 3; $r++) {
            for ($c = 0; $c < 3; $c++) {
                if ($grid[$br + $r][$bc + $c] === $num) return false;
            }
        }
        return true;
    }

    private function removeCells(array $grid, int $count): array
    {
        $attempts = $count;
        while ($attempts > 0) {
            $row = rand(0, 8);
            $col = rand(0, 8);
            if ($grid[$row][$col] !== 0) {
                $grid[$row][$col] = 0;
                $attempts--;
            }
        }
        return $grid;
    }

    private function isSolved(array $board, array $solution): bool
    {
        for ($r = 0; $r < 9; $r++) {
            for ($c = 0; $c < 9; $c++) {
                if ($board[$r][$c] !== $solution[$r][$c]) return false;
            }
        }
        return true;
    }
}