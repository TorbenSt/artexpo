<?php

namespace Tests\Feature\AI\Rag;

use App\AI\Rag\FunFactsRetriever;
use App\Models\RagChunk;
use App\Models\RagDocument;
use App\Models\RagSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FunFactsRetrieverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test sources
        $this->allowedSource = RagSource::create([
            'name' => 'Test Allowed Source',
            'base_url' => 'https://example.com',
            'is_allowed' => true,
        ]);

        $this->disallowedSource = RagSource::create([
            'name' => 'Test Disallowed Source',
            'base_url' => 'https://bad-source.com',
            'is_allowed' => false,
        ]);
    }

    /** @test */
    public function it_retrieves_chunks_from_allowed_sources_only()
    {
        // Create documents
        $allowedDoc = RagDocument::create([
            'rag_source_id' => $this->allowedSource->id,
            'title' => 'Allowed Article',
            'language' => 'de',
            'raw_text' => 'Pablo Picasso war ein spanischer Künstler und Mitbegründer des Kubismus.',
            'checksum' => hash('sha256', 'Pablo Picasso'),
        ]);

        $disallowedDoc = RagDocument::create([
            'rag_source_id' => $this->disallowedSource->id,
            'title' => 'Bad Article',
            'language' => 'de',
            'raw_text' => 'Picasso secret facts that are made up.',
            'checksum' => hash('sha256', 'Picasso secret'),
        ]);

        // Create chunks
        RagChunk::create([
            'rag_document_id' => $allowedDoc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Pablo Picasso war ein spanischer Künstler.',
        ]);

        RagChunk::create([
            'rag_document_id' => $disallowedDoc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Picasso secret facts',
        ]);

        // Retrieve
        $retriever = app(FunFactsRetriever::class);
        $results = $retriever->retrieve('Picasso', 10, 'de');

        // Assert
        $this->assertCount(1, $results, 'Should return at least one result');
        foreach ($results as $result) {
            $this->assertStringNotContainsString('Bad Article', $result['title']);
        }
    }

    /** @test */
    public function it_falls_back_to_english_when_no_german_results()
    {
        // Create English document only
        $enDoc = RagDocument::create([
            'rag_source_id' => $this->allowedSource->id,
            'title' => 'English Article',
            'language' => 'en',
            'raw_text' => 'Vincent van Gogh was a Dutch post-impressionist painter.',
            'checksum' => hash('sha256', 'Van Gogh EN'),
        ]);

        RagChunk::create([
            'rag_document_id' => $enDoc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Vincent van Gogh was a Dutch post-impressionist painter.',
        ]);

        // Retrieve with fallback
        $retriever = app(FunFactsRetriever::class);
        $results = $retriever->retrieveWithFallback('van gogh', 10);

        // Assert: should find English version
        $this->assertGreaterThan(0, count($results));
        $this->assertStringContainsString('Dutch', $results[0]['text']);
    }

    /** @test */
    public function it_prefers_german_over_english()
    {
        // Create both German and English documents
        $deDoc = RagDocument::create([
            'rag_source_id' => $this->allowedSource->id,
            'title' => 'Deutsche Artikel',
            'language' => 'de',
            'raw_text' => 'Leonardo da Vinci war ein italienischer Künstler der Renaissance.',
            'checksum' => hash('sha256', 'Leonardo DE'),
        ]);

        $enDoc = RagDocument::create([
            'rag_source_id' => $this->allowedSource->id,
            'title' => 'English Article',
            'language' => 'en',
            'raw_text' => 'Leonardo da Vinci was an Italian Renaissance artist.',
            'checksum' => hash('sha256', 'Leonardo EN'),
        ]);

        RagChunk::create([
            'rag_document_id' => $deDoc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Leonardo da Vinci war ein italienischer Künstler.',
        ]);

        RagChunk::create([
            'rag_document_id' => $enDoc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Leonardo da Vinci was an Italian artist.',
        ]);

        // Retrieve with fallback
        $retriever = app(FunFactsRetriever::class);
        $results = $retriever->retrieveWithFallback('Leonardo', 10);

        // Assert: should prefer German
        $this->assertStringContainsString('Künstler', $results[0]['text']);
    }

    /** @test */
    public function it_returns_empty_array_on_no_matches()
    {
        $retriever = app(FunFactsRetriever::class);
        $results = $retriever->retrieve('xyzabc notreal', 10, 'de');

        $this->assertEmpty($results);
    }

    /** @test */
    public function it_logs_queries_when_context_is_set()
    {
        $doc = RagDocument::create([
            'rag_source_id' => $this->allowedSource->id,
            'title' => 'Test Doc',
            'language' => 'de',
            'raw_text' => 'Monet was a French impressionist painter.',
            'checksum' => hash('sha256', 'Monet'),
        ]);

        RagChunk::create([
            'rag_document_id' => $doc->id,
            'chunk_index' => 0,
            'chunk_text' => 'Monet was a French impressionist painter.',
        ]);

        // Create an image for FK constraint
        $exhibition = \App\Models\Exhibition::factory()->create();
        $image = \App\Models\Image::factory()->create(['exhibition_id' => $exhibition->id]);

        $retriever = app(FunFactsRetriever::class);
        $retriever->withContext($image->id, 'instagram')->retrieve('Monet', 10, 'de');

        // Check log was created
        $this->assertDatabaseHas('rag_query_logs', [
            'image_id' => $image->id,
            'network' => 'instagram',
        ]);
    }
}
