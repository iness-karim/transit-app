<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class AccessController extends Controller
{
    public function index()
    {
        $drivers = Driver::with('user')->get();
        return Inertia::render('Acces/Index', [
            'drivers' => $drivers,
        ]);
    }

    public function store(Request $request, Driver $driver)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'name' => ($driver->first_name ?? '') . ' ' . ($driver->last_name ?? ''),
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $driver->update(['user_id' => $user->id]);

        return back()->with('success', 'Driver access created successfully.');
    }

    public function destroy(Driver $driver)
    {
        if ($driver->user) {
            $driver->user->delete();
            $driver->update(['user_id' => null]);
        }

        return back()->with('success', 'Driver access revoked.');
    }
}
