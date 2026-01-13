<?php

namespace App\Jobs;

use App\Models\RagChunk;
use App\Models\RagDocument;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ChunkRagDocumentJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(protected RagDocument $document)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $text = $this->document->raw_text;
        $chunkSize = 1000; // characters
        $overlapSize = 150; // overlap for context

        $chunks = $this->splitIntoChunks($text, $chunkSize, $overlapSize);

        foreach ($chunks as $index => $chunkText) {
            RagChunk::create([
                'rag_document_id' => $this->document->id,
                'chunk_index' => $index,
                'chunk_text' => $chunkText,
            ]);
        }

        Log::info("Chunked document {$this->document->id} into " . count($chunks) . ' chunks');
    }

    /**
     * Split text into overlapping chunks.
     *
     * @return array<int, string>
     */
    private function splitIntoChunks(string $text, int $chunkSize, int $overlapSize): array
    {
        $chunks = [];
        $length = strlen($text);

        if ($length <= $chunkSize) {
            return [$text];
        }

        $position = 0;
        $index = 0;

        while ($position < $length) {
            $chunkStart = max(0, $position - ($index > 0 ? $overlapSize : 0));
            $chunkEnd = min($length, $position + $chunkSize);

            $chunk = substr($text, $chunkStart, $chunkEnd - $chunkStart);
            $chunk = trim($chunk);

            if (!empty($chunk)) {
                $chunks[$index] = $chunk;
                $index++;
            }

            $position = $chunkEnd;

            // Avoid infinite loop on very small texts
            if ($position >= $length) {
                break;
            }
        }

        return $chunks;
    }
}
