<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatBotController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
        ]);

        $apiUrl = env('GROK_API_URL', 'https://api.groq.com/openai/v1/chat/completions');
        $apiKey = env('GROQ_API_KEY');
        $model = env('GROK_MODEL', 'llama-3.1-8b-instant');

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post($apiUrl, [
            'model' => $model,
            'messages' => array_merge([
                ['role' => 'system', 'content' => 'You are RAGHD TMS AI, a helpful logistics assistant. Focus on explaining RAGHD TMS features like Precision Routing, Elite Fleet management, and Real-time Pulse. RAGHD TMS is exclusively a Moroccan enterprise operating only within Morocco. All your logistics responses, route examples, and context MUST be tailored specifically to Morocco. However, you are a GENERAL assistant: if the user asks general, non-logistics, or conversational questions, you MUST answer them helpfully in the language they use. Be professional, concise, and futuristic.']
            ], $request->messages),
            'temperature' => 0.7,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'AI Service unavailable'], 503);
        }

        return $response->json();
    }
}
