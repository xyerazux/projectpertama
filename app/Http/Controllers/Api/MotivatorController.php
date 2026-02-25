<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MotivatorController extends Controller
{
    public function motivate(Request $request)
    {
        $request->validate([
            'task_title' => 'required|string|max:255',
        ]);

        $taskTitle = $request->task_title;
        $apiKey = env('GEMINI_API_KEY');

        if (!$apiKey) {
            $fallbacks = [
                "You've got this! One step at a time.",
                "Keep pushing forward! You can achieve this task.",
                "Small progress is still progress. Keep going!",
                "Stay focused and never give up!",
                "You are capable of doing amazing things.",
            ];
            return response()->json([
                'motivation' => $fallbacks[array_rand($fallbacks)]
            ]);
        }

        try {
            $response = Http::timeout(5)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Generate a unique, creative, and short motivational sentence (max 15 words) for the task: {$taskTitle}. Randomize the tone: sometimes coaching, sometimes friendly, or direct. NO EMOJIS. Every response must be different from previous ones."]
                        ]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $motivation = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

                if ($motivation) {
                    return response()->json([
                        'motivation' => trim($motivation)
                    ]);
                }
            }

            Log::error('Gemini API Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('Gemini API Exception: ' . $e->getMessage());
        }

        // Fallback motivation if API fails to prevent freeze
        $fallbacks = [
            "You got this! Focus on the next small step.",
            "Every big achievement is just a series of small steps.",
            "Don't wait for inspiration, create it by starting.",
            "Progress is progress, no matter how small.",
            "A little progress every day adds up to big results.",
            "Believe in yourself and all that you are!",
            "Take a deep breath and keep pushing forward.",
        ];

        return response()->json([
            'motivation' => $fallbacks[array_rand($fallbacks)]
        ]);
    }
}
