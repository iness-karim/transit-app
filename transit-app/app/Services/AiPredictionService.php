<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Route;

class AiPredictionService
{
    protected $apiKey;
    protected $apiUrl;
    protected $model;
    protected $mlApiUrl;

    public function __construct()
    {
        $this->apiKey   = env('GROQ_API_KEY');
        $this->apiUrl   = env('GROK_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
        $this->model    = env('GROK_MODEL', 'llama-3.1-8b-instant');
        // ML microservice base URL (FastAPI running on port 8001)
        $this->mlApiUrl = env('ML_API_URL', 'http://localhost:8001');
    }

    public function predictMissionRisk(Route $route)
    {
        $transport = $route->transport;
        $driver = $route->driver;

        if (!$transport) {
            return ['error' => 'Vehicle information missing for this route.'];
        }

        $prompt = "As an expert logistics analyst, analyze the following mission and predict technical risks, delays, and vehicle state (etat).
        IMPORTANT: RAGHD TMS is a Moroccan logistics enterprise. This mission is taking place exclusively in Morocco. Assume Moroccan roads, highways, typical Moroccan driving conditions, and Moroccan weather patterns when doing your analysis.
        
        VEHICLE DETAILS:
        - Brand: {$transport->brand}
        - Model Year: {$transport->model_year}
        - Current Mileage: {$transport->mileage} KM
        - Last Oil Change (Vidange): {$transport->last_oil_change}
        - Tire Condition: {$transport->tire_condition}
        - Fuel Level: {$transport->fuel_level}%
        
        MISSION CONTEXT:
        - Route: {$route->start_location} to {$route->end_location}
        - Total Distance: {$route->total_distance} KM
        - Cargo: " . ($route->cargo_type ?: 'General Cargo') . "
        - Weather: " . ($route->weather ?: 'Clear') . "
        - Traffic Status: " . ($route->traffic_status ?: 'Fluid') . "
        - Chauffeur Scheduled: " . ($driver ? "{$driver->first_name} {$driver->last_name}" : 'Not Assigned') . "
        
        Provide a COMPREHENSIVE JSON response with:
        1. 'etat_predit': (Success/Caution/High Risk)
        2. 'risk_percentage': (0-100)
        3. 'delay_probability': (Low/Medium/High)
        4. 'breakdown_risk_score': (0-10)
        5. 'fuel_efficiency_est': (Projected fuel consumption rating 0-10)
        6. 'safety_score': (Overall safety rating based on tires/age 0-100)
        7. 'observations': (Detailed professional analytical summary)
        8. 'technical_details': (Dictionary of simple descriptive strings for 'engine', 'tires', 'weather_impact')
        9. 'recommendations': (List of 3-5 specific tactical actions)
        
        The response MUST be valid JSON. Make the analysis feel expert and deep.";

        try {
            $response = Http::timeout(30)->withoutVerifying()->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a professional logistics AI advisor. You MUST respond ONLY with a valid JSON object. No markdown, no conversational text.'],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $content = $response->json()['choices'][0]['message']['content'];
                $data = json_decode($content, true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return ['error' => 'AI returned invalid data format.', 'details' => $content];
                }

                // ── Enrich with ML model prediction (additive, non-breaking) ──
                $mlResult = $this->getMLPrediction($route);
                if ($mlResult !== null) {
                    $data['ml_delay_probability'] = $mlResult['delay_probability'];
                    $data['ml_delay_predicted']   = $mlResult['delay_predicted'];
                    $data['ml_model_used']        = $mlResult['model_used'] ?? 'ML Model';
                }

                return $data;
            }

            $errorData = $response->json();
            return [
                'error' => 'AI Service Response Error',
                'details' => $errorData['error']['message'] ?? $response->body()
            ];
        } catch (\Exception $e) {
            return ['error' => 'Connection to AI Service failed.', 'details' => $e->getMessage()];
        }
    }

    public function generateFleetBriefing($fleetData)
    {
        $prompt = "As a Chief Fleet Operations Officer, provide a high-level strategic briefing based on current fleet data:
        
        FLEET SNAPSHOT:
        - Active Routes: " . count($fleetData['active_routes']) . " currently in progress.
        - Total Drivers: {$fleetData['driver_count']} available in pool.
        - Vehicle States: " . json_encode($fleetData['vehicle_health']) . "
        
        RECENT PERFORMANCE:
        - Total Completed Missions: {$fleetData['completed_count']}
        - Punctuality Rate: {$fleetData['on_time_rate']}%
        
        Provide a JSON response with:
        1. 'headline': (A catchy executive summary)
        2. 'critical_alerts': (List of simple descriptive strings for urgent issues)
        3. 'top_performer': (Identify best driver or vehicle based on data)
        4. 'strategic_advice': (One major piece of advice for the day)
        5. 'pulse_score': (0-100 overall fleet health)
        
        Respond only with valid JSON.";

        return $this->callGroq($prompt, 'Strategic Fleet Advisor');
    }

    public function predictMaintenanceTimeline($transport)
    {
        $prompt = "Predict the next critical maintenance for this vehicle using its full profile:
        Vehicle: {$transport->brand} ({$transport->model_year})
        Current Mileage: {$transport->mileage} KM
        Last Service: {$transport->last_oil_change}
        Tire Condition: {$transport->tire_condition}
        Fuel Level: {$transport->fuel_level}%
        Capacity: {$transport->capacity} Tons
        
        Provide a JSON response with:
        1. 'next_service_estimate': (Date string)
        2. 'days_remaining': (integer)
        3. 'priority': (Low/Medium/High)
        4. 'reasoning': (Deep technical explanation based on mileage and state)
        
        Respond only with valid JSON.";

        return $this->callGroq($prompt, 'Maintenance Forecaster');
    }

    public function recommendDriversForMission(Route $route, $availableDrivers)
    {
        $prompt = "Analyze the mission and select the absolute best candidates from the pool:
        MISSION: {$route->start_location} to {$route->end_location}
        DISTANCE: {$route->total_distance} KM
        CARGO: {$route->cargo_type}
        
        FULL DRIVER POOL DATA:
        " . json_encode($availableDrivers) . "
        
        Evaluation Criteria:
        - Mission Complexity vs Driver Experience.
        - Proximity/Availability.
        - Previous Performance Metadata.
        
        Provide a JSON response with:
        1. 'recommendations': (List of 3 objects with 'driver_id', 'match_score' (0-100), 'reasoning')
        2. 'dispatch_briefing': (Executive dispatch strategy summary)
        
        Respond only with valid JSON.";

        return $this->callGroq($prompt, 'Expert Dispatcher');
    }

    public function predictDriverHealth($driver, $todayDistance)
    {
        $age = $driver->date_of_birth ? now()->diffInYears($driver->date_of_birth) : 'Unknown';
        
        $prompt = "As a medical and logistics safety expert, analyze this chauffeur's state for a prospective mission.
        
        CHAUFFEUR DETAILS:
        - Name: {$driver->first_name} {$driver->last_name}
        - Age: {$age}
        - Distance Covered Today: {$todayDistance} KM
        - Status: {$driver->status}
        - Experience Level: " . ($driver->contract_start ? now()->diffInYears($driver->contract_start) . " years" : 'N/A') . "
        
        Provide a JSON response with:
        1. 'fatigue_level': (No Fatigue/Slight/Tired/Exhausted)
        2. 'health_score': (0-100 overall physical readiness)
        3. 'can_proceed': (Boolean)
        4. 'reasoning': (Deep analysis of age vs mileage vs daily capacity)
        5. 'recommendation': (Specific instruction: 'Go', 'Take 30m break', 'Stop for the day')
        
        Respond only with valid JSON.";

        return $this->callGroq($prompt, 'Driver Safety & Health Analyst');
    }

    protected function callGroq($prompt, $systemRole = 'Logistics AI Advisor')
    {
        try {
            $response = Http::timeout(30)->withoutVerifying()->withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])->post($this->apiUrl, [
                'model' => $this->model,
                'messages' => [
                    ['role' => 'system', 'content' => "You are a professional {$systemRole}. Respond ONLY with valid JSON. No markdown. Access current date: " . date('Y-m-d')],
                    ['role' => 'user', 'content' => $prompt]
                ],
                'temperature' => 0.3,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                return json_decode($response->json()['choices'][0]['message']['content'], true);
            }
            return ['error' => 'AI Service Error'];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Call the FastAPI ML microservice to get a classical ML delay probability.
     * Returns null silently if the service is unavailable — Groq analysis continues normally.
     */
    private function getMLPrediction(Route $route): ?array
    {
        $transport = $route->transport;
        $driver    = $route->driver;

        // Map vehicle condition from raw text to model categories
        $vehicleCondition = 'good';
        if ($transport) {
            $tire = strtolower($transport->tire_condition ?? '');
            $fuel = $transport->fuel_level ?? 100;
            if (str_contains($tire, 'worn') || str_contains($tire, 'poor') || $fuel < 25) {
                $vehicleCondition = 'poor';
            } elseif (str_contains($tire, 'fair') || $fuel < 50) {
                $vehicleCondition = 'fair';
            }
        }

        // Map weather field
        $weatherRaw = strtolower($route->weather ?? 'clear');
        $weather = 'clear';
        foreach (['storm', 'fog', 'rain'] as $w) {
            if (str_contains($weatherRaw, $w)) {
                $weather = $w;
                break;
            }
        }

        // Map cargo type
        $cargoRaw = strtolower($route->cargo_type ?? 'general');
        $cargoMap = ['electronics' => 'electronics', 'food' => 'food', 'hazmat' => 'hazmat', 'furniture' => 'furniture'];
        $cargoType = 'general';
        foreach ($cargoMap as $key => $val) {
            if (str_contains($cargoRaw, $key)) {
                $cargoType = $val;
                break;
            }
        }

        $payload = [
            'distance_km'             => (float) ($route->total_distance ?? 100),
            'cargo_type'              => $cargoType,
            'weather'                 => $weather,
            'hour_of_departure'       => (int) now()->format('G'),
            'day_of_week'             => (int) now()->dayOfWeek,   // 0=Sun in Carbon → remap
            'vehicle_condition'       => $vehicleCondition,
            'driver_experience_years' => $driver
                ? (int) now()->diffInYears($driver->contract_start ?? now())
                : 2,
        ];

        // Carbon dayOfWeek: 0=Sun,1=Mon…6=Sat → convert to 0=Mon…6=Sun
        $payload['day_of_week'] = ($payload['day_of_week'] + 6) % 7;

        try {
            $response = Http::timeout(5)->post($this->mlApiUrl . '/predict', $payload);
            if ($response->successful()) {
                return $response->json();
            }
            return null;
        } catch (\Exception $e) {
            // FastAPI is offline — degrade gracefully
            return null;
        }
    }
}
