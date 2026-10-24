<?php

namespace App\Http\Controllers;

use App\Models\Route;
use App\Models\Transport;
use App\Models\Driver;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        if (request()->user() && request()->user()->driver()->exists()) {
            return redirect()->route('driver.dashboard');
        }

        $avgFuel = Transport::avg('fuel_level') ?? 0;
        $totalMileage = Transport::sum('mileage');

        return Inertia::render('Dashboard', [
            'stats' => [
                'transports' => Transport::count(),
                'drivers' => Driver::count(),
                'routes' => Route::where('status', 'in_progress')->count(),
                'fuel_avg' => round($avgFuel) . '%',
                'total_mileage' => number_format($totalMileage) . ' KM',
            ],
            'weekly_missions' => Route::selectRaw('DATE(created_at) as date, count(*) as count')
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(fn($m) => [
                    'date' => \Carbon\Carbon::parse($m->date)->format('M d'),
                    'count' => $m->count
                ]),
            'recent_operations' => Route::with(['transport', 'driver'])->latest()->take(5)->get(),
            'transports' => Transport::all(), // For maintenance prediction on frontend
        ]);
    }

    public function briefing(Request $request, \App\Services\AiPredictionService $aiService)
    {
        $completedRoutes = Route::where('status', 'completed')->get();
        $totalCompleted = $completedRoutes->count();
        $onTimeCount = 0;

        foreach ($completedRoutes as $r) {
            if ($r->actual_arrival && $r->estimated_arrival) {
                if (new \DateTime($r->actual_arrival) <= new \DateTime($r->estimated_arrival)) {
                    $onTimeCount++;
                }
            }
        }

        $onTimeRate = $totalCompleted > 0 ? round(($onTimeCount / $totalCompleted) * 100) : 100;

        $fleetData = [
            'active_routes' => Route::where('status', 'in_progress')->with(['transport', 'driver'])->get(),
            'vehicle_health' => Transport::select('brand', 'mileage', 'fuel_level', 'tire_condition', 'model_year')->get(),
            'driver_count' => Driver::count(),
            'completed_count' => $totalCompleted,
            'on_time_rate' => $onTimeRate,
        ];

        return response()->json($aiService->generateFleetBriefing($fleetData));
    }

    public function predictMaintenance(Transport $transport, \App\Services\AiPredictionService $aiService)
    {
        return response()->json($aiService->predictMaintenanceTimeline($transport));
    }

    public function matchmaker()
    {
        return Inertia::render('AiMatchmaker', [
            'unassigned_routes' => Route::whereNull('driver_id')->get(),
            'available_drivers' => Driver::where('status', 'active')->get(),
        ]);
    }

    public function predictDriverHealth(Driver $driver, \App\Services\AiPredictionService $aiService)
    {
        // Calculate distance covered today by checking routes assigned to this driver
        $todayDistance = Route::where('driver_id', $driver->id)
            ->whereDate('created_at', now()->toDateString())
            ->sum('total_distance');

        return response()->json($aiService->predictDriverHealth($driver, $todayDistance));
    }

    public function getRecommendations(Route $route, \App\Services\AiPredictionService $aiService)
    {
        $drivers = Driver::where('status', 'active')
            ->select('id', 'first_name', 'last_name', 'phone', 'license_number', 'contract_start')
            ->get()
            ->map(function($driver) {
                $years = $driver->contract_start ? now()->diffInYears($driver->contract_start) : 0;
                $driver->experience = $years . " years with company";
                return $driver;
            });

        return response()->json($aiService->recommendDriversForMission($route, $drivers));
    }
}
