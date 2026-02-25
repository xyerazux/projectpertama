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
            return response()->json([
                'motivation' => 'Keep pushing forward! You can achieve this task.'
            ]);
        }

        try {
            $response = Http::timeout(5)->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => "Give a 1-sentence motivational tip (max 15 words) for the task: {$taskTitle}. No emojis."]
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
        return response()->json([
            'motivation' => 'You got this! Focus on the next small step.'
        ]);
    }
}
