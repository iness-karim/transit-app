<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function index()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return redirect()->route('dashboard')->with('error', 'No driver profile linked to this account.');
        }

        // Active mission - Using 'ongoing' status
        $activeMission = Route::where('driver_id', $driver->id)
            ->where('status', 'ongoing')
            ->with(['transport'])
            ->first();

        return Inertia::render('Drivers/ActiveMission', [
            'mission' => $activeMission,
            'driver' => $driver
        ]);
    }

    public function performance()
    {
        $driver = Auth::user()->driver;

        if (!$driver) {
            return redirect()->route('dashboard')->with('error', 'No driver profile linked to this account.');
        }

        $missions = Route::where('driver_id', $driver->id)
            ->with(['transport'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Stats calculation
        $totalMissions = $missions->count();
        $completedMissions = $missions->where('status', 'completed')->count();
        $successRate = $totalMissions > 0 ? round(($completedMissions / $totalMissions) * 100) : 0;
        
        // Performance History (Cumulative for Chart)
        $runningTotal = $driver->points - $missions->filter(fn($m) => $m->status === 'completed' || $m->status === 'failed')->sum(function($m) {
            if ($m->status === 'completed') return ($m->delivery_date <= $m->estimated_delivery_date) ? 2 : 0;
            if ($m->status === 'failed') return -1;
            return 0;
        });

        $pointsAtStep = $runningTotal;
        $performanceHistory = $missions->sortBy('created_at')->take(10)->map(function($m) use (&$pointsAtStep) {
            $pointsFromThis = 0;
            if ($m->status === 'completed') {
                $pointsFromThis = ($m->delivery_date <= $m->estimated_delivery_date) ? 2 : 0;
            } elseif ($m->status === 'failed') {
                $pointsFromThis = -1;
            }
            $pointsAtStep += $pointsFromThis;
            return [
                'date' => $m->created_at->format('M d'),
                'points' => $pointsAtStep,
                'status' => $m->status
            ];
        })->values();

        return Inertia::render('Drivers/Performance', [
            'missions' => $missions,
            'driver' => $driver,
            'stats' => [
                'total' => $totalMissions,
                'success_rate' => $successRate,
                'points' => $driver->points,
                'history' => $performanceHistory
            ]
        ]);
    }

    public function updateTime(Request $request, Route $route)
    {
        $request->validate([
            'field' => 'required|in:departure_date,delivery_date,return_date,status',
            'time' => 'nullable|date',
            'status' => 'nullable|string'
        ]);

        $updateData = [];
        if ($request->has('field') && $request->field !== 'status') {
            $updateData[$request->field] = $request->time;
        }

        if ($request->status) {
            $updateData['status'] = $request->status;
        }

        $route->update($updateData);

        // Update Driver Points if mission completed/failed
        if (isset($updateData['status'])) {
            $driver = $route->driver;
            $pointsToAdd = 0;
            
            if ($updateData['status'] === 'completed') {
                $pointsToAdd = ($route->delivery_date <= $route->estimated_delivery_date) ? 2 : 0;
            } elseif ($updateData['status'] === 'failed') {
                $pointsToAdd = -1;
            }
            
            $driver->increment('points', $pointsToAdd);
        }

        return back()->with('success', 'Mission status updated.');
    }
}
