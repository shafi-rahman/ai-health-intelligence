<?php

namespace App\Jobs;

use App\Models\Document;
use App\Services\Health\MedicalAnalyzerService;
use App\Services\Health\PrescriptionAnalyzerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AnalyzeMedicalDocumentJob implements ShouldQueue
{
    use Queueable;

    public int $timeout = 300;
    public int $tries   = 2;

    public function __construct(private Document $document) {}

    public function handle(
        MedicalAnalyzerService $medicalAnalyzer,
        PrescriptionAnalyzerService $prescriptionAnalyzer
    ): void {
        // Reconstruct full text from stored chunks
        $text = $this->document->chunks()
            ->orderBy('chunk_index')
            ->pluck('content')
            ->implode("\n\n");

        if (empty(trim($text))) {
            return;
        }

        // Truncate to ~6000 words to stay within Ollama context limits
        $words = preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY);
        if (count($words) > 6000) {
            $text = implode(' ', array_slice($words, 0, 6000));
        }

        try {
            $result = match ($this->document->category) {
                'medical_report' => $medicalAnalyzer->analyze($text),
                'prescription'   => $prescriptionAnalyzer->analyze($text),
                default          => null,
            };

            if ($result !== null) {
                $this->document->update(['analysis_result' => $result]);
            }
        } catch (\Throwable $e) {
            // Analysis is best-effort — do not fail the RAG pipeline over it
            Log::warning("Health analysis failed for document {$this->document->id}: {$e->getMessage()}");
        }
    }
}
