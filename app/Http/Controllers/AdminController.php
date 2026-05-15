<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AdminController extends Controller
{
    public function index()
    {
        $users = User::orderBy('created_at', 'desc')->get();
        return view('admin', compact('users'));
    }

    public function createUser(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', Password::min(8)],
            'role'     => 'in:user,admin',
        ]);

        User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => $request->role ?? 'user',
        ]);

        return back()->with('success', 'User created.');
    }

    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id,
            'role'  => 'in:user,admin',
        ]);

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->role  = $request->role;

        if ($request->filled('password')) {
            $request->validate(['password' => Password::min(8)]);
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'User updated.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        abort_if($user->id === auth()->id(), 403, "Can't delete yourself.");
        $user->delete();
        return back()->with('success', 'User deleted.');
    }
}