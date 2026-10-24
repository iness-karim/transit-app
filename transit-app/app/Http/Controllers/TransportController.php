<?php

namespace App\Http\Controllers;

use App\Models\Transport;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransportController extends Controller
{
    public function index()
    {
        return Inertia::render('Transports/Index', [
            'transports' => Transport::latest()->get()
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|unique:transports,plate_number',
            'icon_predict' => 'nullable|string',
            'status' => 'required|string',
            'brand' => 'nullable|string',
            'model_year' => 'nullable|integer',
            'mileage' => 'nullable|numeric',
            'last_oil_change' => 'nullable|date',
            'capacity' => 'nullable|string',
            'tire_condition' => 'nullable|string',
            'fuel_level' => 'nullable|integer',
        ]);

        Transport::create($validated);

        return redirect()->back()->with('message', 'Transport added successfully.');
    }

    public function update(Request $request, Transport $transport)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|unique:transports,plate_number,' . $transport->id,
            'icon_predict' => 'nullable|string',
            'status' => 'required|string',
            'brand' => 'nullable|string',
            'model_year' => 'nullable|integer',
            'mileage' => 'nullable|numeric',
            'last_oil_change' => 'nullable|date',
            'capacity' => 'nullable|string',
            'tire_condition' => 'nullable|string',
            'fuel_level' => 'nullable|integer',
        ]);

        $transport->update($validated);

        return redirect()->back()->with('message', 'Transport updated successfully.');
    }

    public function destroy(Transport $transport)
    {
        $transport->delete();

        return redirect()->back()->with('message', 'Transport deleted successfully.');
    }
}
