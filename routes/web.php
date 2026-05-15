<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CatchController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChessController;
use App\Http\Controllers\MemoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RpsController;
use App\Http\Controllers\SudokuController;
use App\Http\Controllers\TictactoeController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'loginPage']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout']);


Route::middleware('auth')->group(function () {
    Route::get('/chat',          [ChatController::class, 'index']);
    Route::post('/chat/send',    [ChatController::class, 'send']);
    Route::get('/chat/messages',      [ChatController::class, 'poll']);
    Route::post('/chat/typing',   [ChatController::class, 'typing']);
    Route::get('/chat/who-typing', [ChatController::class, 'whoTyping']);
    Route::delete('/chat/{id}',  [ChatController::class, 'delete']);
    Route::post('/chat/{id}/undo', [ChatController::class, 'undoDelete']);
    Route::patch('/chat/{id}',   [ChatController::class, 'edit']);
    Route::get('/chat/{id}/get', [ChatController::class, 'getOne']);
    Route::get('/profile', [ProfileController::class, 'edit']);
    Route::patch('/profile', [ProfileController::class, 'update']);
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword']);


    Route::middleware('App\Http\Middleware\AdminMiddleware')->group(function () {
        Route::get('/admin', [AdminController::class, 'index']);
        Route::post('/admin/users', [AdminController::class, 'createUser']);
        Route::patch('/admin/users/{id}', [AdminController::class, 'updateUser']);
        Route::delete('/admin/users/{id}', [AdminController::class, 'deleteUser']);
        Route::get('/register', [AuthController::class, 'registerPage']);
        Route::post('/register', [AuthController::class, 'register']);
    });

    Route::get('/games/chess',        [ChessController::class, 'index']);
    Route::get('/games/chess/state',  [ChessController::class, 'state']);
    Route::post('/games/chess/move',  [ChessController::class, 'move']);
    Route::post('/games/chess/reset', [ChessController::class, 'reset']);

    Route::get('/games/catch',              [CatchController::class, 'index']);
    Route::get('/games/catch/state',        [CatchController::class, 'state']);
    Route::get('/games/catch/leaderboard',  [CatchController::class, 'leaderboard']); // ← this one
    Route::post('/games/catch/join',        [CatchController::class, 'join']);
    Route::post('/games/catch/start',       [CatchController::class, 'start']);
    Route::post('/games/catch/score',       [CatchController::class, 'score']);
    Route::post('/games/catch/reset',       [CatchController::class, 'reset']);

    Route::get('/games/sudoku',        [SudokuController::class, 'index']);
    Route::get('/games/sudoku/state',  [SudokuController::class, 'state']);
    Route::post('/games/sudoku/start', [SudokuController::class, 'start']);
    Route::post('/games/sudoku/move',  [SudokuController::class, 'move']);
    Route::post('/games/sudoku/pause', [SudokuController::class, 'pause']);
    Route::post('/games/sudoku/reset', [SudokuController::class, 'reset']);

    Route::get('/games/tictactoe',        [TictactoeController::class, 'index']);
    Route::get('/games/tictactoe/state',  [TictactoeController::class, 'state']);
    Route::post('/games/tictactoe/move',  [TictactoeController::class, 'move']);
    Route::post('/games/tictactoe/reset', [TictactoeController::class, 'reset']);

    Route::get('/games/rps',          [RpsController::class, 'index']);
    Route::get('/games/rps/state',    [RpsController::class, 'state']);
    Route::post('/games/rps/join',    [RpsController::class, 'join']);
    Route::post('/games/rps/pick',    [RpsController::class, 'pick']);
    Route::post('/games/rps/clash',   [RpsController::class, 'clash']);
    Route::post('/games/rps/reset',   [RpsController::class, 'reset']);
    Route::post('/games/rps/leave',   [RpsController::class, 'leave']);

    Route::get('/games/memory',              [MemoryController::class, 'index']);
    Route::get('/games/memory/state',        [MemoryController::class, 'state']);
    Route::post('/games/memory/start',       [MemoryController::class, 'start']);
    Route::post('/games/memory/flip',        [MemoryController::class, 'flip']);
    Route::post('/games/memory/resolve',     [MemoryController::class, 'resolve']);
    Route::post('/games/memory/player',      [MemoryController::class, 'addPlayer']);
    Route::delete('/games/memory/player/{id}', [MemoryController::class, 'removePlayer']);

});

Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web');