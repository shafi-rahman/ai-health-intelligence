<?php

namespace App\Services\Health;

use App\Services\AI\Providers\OllamaProvider;

class HealthRecommendationService
{
    public function __construct(private OllamaProvider $ollama) {}

    public function generate(array $medicalAnalysis, array $prescriptionData = [], string $modelKey = 'phi'): array
    {
        $model = config("ai.providers.ollama.models.{$modelKey}", 'phi:latest');

        $context  = "Medical Report Summary: " . ($medicalAnalysis['summary'] ?? 'Not available') . "\n";
        $context .= "Key Findings: " . implode(', ', $medicalAnalysis['key_findings'] ?? []) . "\n";
        $context .= "Risk Level: " . ($medicalAnalysis['risk_level'] ?? 'unknown') . "\n";

        if (!empty($prescriptionData['medicines'])) {
            $meds     = array_map(fn ($m) => "{$m['name']} ({$m['dosage']}, {$m['frequency']})", $prescriptionData['medicines']);
            $context .= "Current Medications: " . implode(', ', $meds) . "\n";
        }

        $prompt = <<<PROMPT
Based on the following health context, provide personalized general wellness recommendations.

Health Context:
{$context}

Return ONLY valid JSON in this exact format (no markdown, no extra text):
{
  "diet_plan": ["dietary suggestion 1", "dietary suggestion 2"],
  "lifestyle_changes": ["lifestyle suggestion 1", "lifestyle suggestion 2"],
  "exercise_plan": ["exercise suggestion 1", "exercise suggestion 2"],
  "daily_routine": ["routine tip 1", "routine tip 2"],
  "disclaimer": "These are general wellness suggestions only, not medical treatment. Consult your healthcare provider before making any health changes."
}
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a wellness advisor. Provide safe, general lifestyle and health recommendations based on health data. Never diagnose, prescribe, or recommend medication changes. Return only valid JSON with no markdown code blocks.',
            ],
            ['role' => 'user', 'content' => $prompt],
        ];

        $raw     = $this->ollama->generate($messages, $model);
        $content = trim($raw['message']['content'] ?? '');
        $result  = $this->extractJson($content);

        $result['disclaimer'] ??= config('ai.medical_disclaimer');

        return $result;
    }

    private function extractJson(string $content): array
    {
        $content = preg_replace('/```(?:json)?\s*([\s\S]*?)```/m', '$1', $content);

        if (preg_match('/\{[\s\S]*\}/m', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        return [
            'diet_plan'         => [],
            'lifestyle_changes' => [],
            'exercise_plan'     => [],
            'daily_routine'     => [],
            'disclaimer'        => config('ai.medical_disclaimer'),
        ];
    }
}
