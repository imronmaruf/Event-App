<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('unit')->orderBy('name')->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.create', compact('units'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'role'      => 'required|in:superadmin,admin',
            'unit_id'   => 'nullable|required_if:role,admin|exists:units,id',
            'is_active' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Admin berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $units = Unit::where('is_active', true)->orderBy('name')->get();
        return view('admin.users.edit', compact('user', 'units'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:150',
            'email'     => "required|email|unique:users,email,{$user->id}",
            'password'  => 'nullable|string|min:8|confirmed',
            'role'      => 'required|in:superadmin,admin',
            'unit_id'   => 'nullable|required_if:role,admin|exists:units,id',
            'is_active' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        $validated['is_active'] = $request->boolean('is_active');

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data admin berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }
        $user->delete();
        return back()->with('success', 'Admin berhasil dihapus.');
    }
}
