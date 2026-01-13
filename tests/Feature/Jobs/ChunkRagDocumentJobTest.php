<?php

namespace Tests\Feature\Jobs;

use App\Jobs\ChunkRagDocumentJob;
use App\Models\RagChunk;
use App\Models\RagDocument;
use App\Models\RagSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChunkRagDocumentJobTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_chunks_document_into_smaller_pieces()
    {
        $source = RagSource::create([
            'name' => 'Test Source',
            'is_allowed' => true,
        ]);

        $longText = str_repeat('Lorem ipsum dolor sit amet. ', 100); // ~2800 characters

        $doc = RagDocument::create([
            'rag_source_id' => $source->id,
            'title' => 'Long Document',
            'language' => 'de',
            'raw_text' => $longText,
            'checksum' => hash('sha256', $longText),
        ]);

        // Execute job
        (new ChunkRagDocumentJob($doc))->handle();

        // Assert chunks were created
        $chunks = RagChunk::where('rag_document_id', $doc->id)->get();
        $this->assertGreaterThan(1, $chunks->count());

        // Each chunk should be reasonable size
        foreach ($chunks as $chunk) {
            $this->assertGreaterThan(0, strlen($chunk->chunk_text));
            $this->assertLessThan(2000, strlen($chunk->chunk_text)); // Max 2000 chars
        }
    }

    /** @test */
    public function it_handles_short_documents_as_single_chunk()
    {
        $source = RagSource::create([
            'name' => 'Test Source',
            'is_allowed' => true,
        ]);

        $shortText = 'This is a short document.';

        $doc = RagDocument::create([
            'rag_source_id' => $source->id,
            'title' => 'Short Document',
            'language' => 'de',
            'raw_text' => $shortText,
            'checksum' => hash('sha256', $shortText),
        ]);

        // Execute job
        (new ChunkRagDocumentJob($doc))->handle();

        // Assert single chunk
        $chunks = RagChunk::where('rag_document_id', $doc->id)->get();
        $this->assertEquals(1, $chunks->count());
        $this->assertStringContainsString('short document', strtolower($chunks[0]->chunk_text));
    }

    /** @test */
    public function it_preserves_chunk_order()
    {
        $source = RagSource::create([
            'name' => 'Test Source',
            'is_allowed' => true,
        ]);

        $text = 'First part. ' . str_repeat('Middle content. ', 50) . ' Last part.';

        $doc = RagDocument::create([
            'rag_source_id' => $source->id,
            'title' => 'Ordered Document',
            'language' => 'de',
            'raw_text' => $text,
            'checksum' => hash('sha256', $text),
        ]);

        // Execute job
        (new ChunkRagDocumentJob($doc))->handle();

        // Assert chunks are in order
        $chunks = RagChunk::where('rag_document_id', $doc->id)
            ->orderBy('chunk_index')
            ->get();

        for ($i = 0; $i < $chunks->count(); $i++) {
            $this->assertEquals($i, $chunks[$i]->chunk_index);
        }
    }
}
