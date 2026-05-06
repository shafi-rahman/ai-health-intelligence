<?php

namespace App\Services\Health;

use App\Services\AI\Providers\OllamaProvider;

class MedicalAnalyzerService
{
    public function __construct(private OllamaProvider $ollama) {}

    public function analyze(string $text, string $modelKey = 'phi'): array
    {
        $model = config("ai.providers.ollama.models.{$modelKey}", 'phi:latest');

        $prompt = <<<PROMPT
Analyze the following medical report text and extract structured health insights.

Return ONLY valid JSON in this exact format (no markdown, no extra text):
{
  "summary": "Brief overview of the medical report",
  "key_findings": ["finding 1", "finding 2"],
  "possible_concerns": ["concern 1", "concern 2"],
  "risk_level": "low",
  "recommendations": ["recommendation 1", "recommendation 2"],
  "disclaimer": "This tool provides informational insights only and is not a substitute for professional medical advice. Please consult a qualified healthcare provider."
}

Risk level must be one of: low, medium, high.

Medical Report Text:
{$text}
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a medical information analyst. Extract and summarize health information from medical documents. Never diagnose. Always include a disclaimer. Return only valid JSON with no markdown code blocks.',
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
        // Strip markdown code fences if present
        $content = preg_replace('/```(?:json)?\s*([\s\S]*?)```/m', '$1', $content);

        if (preg_match('/\{[\s\S]*\}/m', $content, $matches)) {
            $decoded = json_decode($matches[0], true);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        return [
            'summary'           => $content,
            'key_findings'      => [],
            'possible_concerns' => [],
            'risk_level'        => 'unknown',
            'recommendations'   => [],
            'disclaimer'        => config('ai.medical_disclaimer'),
        ];
    }
}
