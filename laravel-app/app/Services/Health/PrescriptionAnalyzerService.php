<?php

namespace App\Services\Health;

use App\Services\AI\Providers\OllamaProvider;

class PrescriptionAnalyzerService
{
    public function __construct(private OllamaProvider $ollama) {}

    public function analyze(string $text, string $modelKey = 'phi'): array
    {
        $model = config("ai.providers.ollama.models.{$modelKey}", 'phi:latest');

        $prompt = <<<PROMPT
Analyze the following prescription text and extract medicine information.

Return ONLY valid JSON in this exact format (no markdown, no extra text):
{
  "medicines": [
    {
      "name": "Medicine name",
      "dosage": "Dosage amount",
      "frequency": "How often to take it",
      "purpose": "General informational purpose of this medicine"
    }
  ],
  "notes": ["Any additional instructions from the prescription"],
  "warnings": ["Any precautions or warnings"],
  "disclaimer": "This tool provides informational insights only and is not a substitute for professional medical advice. Please consult a qualified healthcare provider."
}

Prescription Text:
{$text}
PROMPT;

        $messages = [
            [
                'role'    => 'system',
                'content' => 'You are a pharmaceutical information assistant. Extract prescription details and explain medicines in general terms. Never make medical claims or recommend dosage changes. Return only valid JSON with no markdown code blocks.',
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
            'medicines'  => [],
            'notes'      => [$content],
            'warnings'   => [],
            'disclaimer' => config('ai.medical_disclaimer'),
        ];
    }
}
