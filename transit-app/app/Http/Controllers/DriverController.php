<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DriverController extends Controller
{
    public function index()
    {
        return Inertia::render('Drivers/Index', [
            'drivers' => Driver::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'status' => 'required|string',
            'phone' => 'nullable|string',
            'license_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
        ]);

        Driver::create($validated);

        return redirect()->back()->with('message', 'Driver added successfully.');
    }

    public function update(Request $request, Driver $driver)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'contract_start' => 'nullable|date',
            'contract_end' => 'nullable|date',
            'status' => 'required|string',
            'phone' => 'nullable|string',
            'license_number' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'emergency_contact' => 'nullable|string',
        ]);

        $driver->update($validated);

        return redirect()->back()->with('message', 'Driver updated successfully.');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();

        return redirect()->back()->with('message', 'Driver deleted successfully.');
    }
}
