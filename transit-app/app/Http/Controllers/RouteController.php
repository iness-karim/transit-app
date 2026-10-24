<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Transport;
use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Notification;
use App\Notifications\SystemNotification;

class RouteController extends Controller
{
    public function index()
    {
        return Inertia::render('Routes/Index', [
            'routes' => Route::with(['transport', 'driver'])->latest()->get(),
            'transports' => Transport::where('status', 'active')->get(),
            'drivers' => Driver::where('status', 'active')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transport_id' => 'required|exists:transports,id',
            'driver_id' => 'required|exists:drivers,id',
            'start_location' => 'required|string',
            'end_location' => 'required|string',
            'status' => 'required|string',
            'cargo_type' => 'nullable|string',
            'estimated_arrival' => 'nullable|date',
            'total_distance' => 'nullable|numeric',
            'weather' => 'nullable|string',
            'traffic_status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        Route::create($validated);

        return redirect()->back()->with('message', 'Route initialized successfully.');
    }

    public function update(Request $request, Route $route)
    {
        $validated = $request->validate([
            'status' => 'required|string',
            'current_lat' => 'nullable|numeric',
            'current_lng' => 'nullable|numeric',
            'cargo_type' => 'nullable|string',
            'estimated_arrival' => 'nullable|date',
            'total_distance' => 'nullable|numeric',
            'weather' => 'nullable|string',
            'traffic_status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        // Mileage & Arrival Logic
        if ($validated['status'] === 'completed' && $route->status !== 'completed') {
            $route->actual_arrival = now();
            $transport = $route->transport;
            if ($transport) {
                // Use the total_distance from the request if provided, otherwise from the model
                $distance = $validated['total_distance'] ?? $route->total_distance ?? 0;
                $transport->increment('mileage', $distance);
            }
            
            $admins = User::role('superadmin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new SystemNotification(
                    'Trajet Terminé',
                    "La mission de {$route->start_location} vers {$route->end_location} a été livrée avec succès.",
                    'success'
                ));
            }
        } elseif ($validated['status'] === 'cancelled' && $route->status !== 'cancelled') {
            $admins = User::role('superadmin')->get();
            if ($admins->isNotEmpty()) {
                Notification::send($admins, new SystemNotification(
                    'Trajet Annulé',
                    "La mission de {$route->start_location} vers {$route->end_location} a été annulée.",
                    'warning'
                ));
            }
        }

        $route->fill($validated);
        $route->save();

        return redirect()->back()->with('message', 'Route updated successfully.');
    }

    public function deliveries()
    {
        return Inertia::render('Deliveries/Index', [
            'deliveries' => Route::with(['transport', 'driver'])
                ->whereIn('status', ['completed', 'cancelled'])
                ->latest('actual_arrival')
                ->get()
        ]);
    }

    public function predict(Route $route, \App\Services\AiPredictionService $aiService)
    {
        $prediction = $aiService->predictMissionRisk($route);
        return response()->json($prediction);
    }

    public function destroy(Route $route)
    {
        $route->delete();

        return redirect()->back()->with('message', 'Route deleted successfully.');
    }
}
