<?php

namespace Tests\Feature\Console\Commands;

use App\Models\RagDocument;
use App\Models\RagSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class RagImportCommandTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_imports_document_from_file()
    {
        $testFile = storage_path('test-import.txt');
        File::put($testFile, 'This is test content about Leonardo da Vinci.');

        try {
            $this->artisan('rag:import', [
                '--source' => 'Test Source',
                '--title' => 'Test Document',
                '--file' => $testFile,
                '--language' => 'de',
            ])->assertSuccessful();

            // Assert document was created
            $this->assertDatabaseHas('rag_documents', [
                'title' => 'Test Document',
                'language' => 'de',
            ]);

            // Assert source was created
            $this->assertDatabaseHas('rag_sources', [
                'name' => 'Test Source',
                'is_allowed' => true,
            ]);
        } finally {
            File::delete($testFile);
        }
    }

    /** @test */
    public function it_requires_source_and_title()
    {
        $this->artisan('rag:import')
            ->expectsOutput('--source is required')
            ->assertFailed();
    }

    /** @test */
    public function it_prevents_duplicate_documents()
    {
        $testFile = storage_path('test-import.txt');
        $content = 'Unique test content for deduplication.';
        File::put($testFile, $content);

        try {
            // First import
            $this->artisan('rag:import', [
                '--source' => 'Test Source',
                '--title' => 'Document 1',
                '--file' => $testFile,
            ])->assertSuccessful();

            // Second import (same content)
            $this->artisan('rag:import', [
                '--source' => 'Test Source',
                '--title' => 'Document 2',
                '--file' => $testFile,
            ])->expectsOutputToContain('Document already exists')
                ->assertSuccessful();

            // Assert only one document exists
            $this->assertEquals(1, RagDocument::count());
        } finally {
            File::delete($testFile);
        }
    }

    /** @test */
    public function it_sets_optional_metadata()
    {
        $testFile = storage_path('test-import.txt');
        File::put($testFile, 'Content about a specific artist.');

        try {
            $this->artisan('rag:import', [
                '--source' => 'Test Source',
                '--title' => 'Test Document',
                '--file' => $testFile,
                '--artist' => 'Michelangelo',
                '--url' => 'https://example.com/doc',
            ])->assertSuccessful();

            // Assert metadata was saved
            $this->assertDatabaseHas('rag_documents', [
                'title' => 'Test Document',
                'artist_name' => 'Michelangelo',
                'url' => 'https://example.com/doc',
            ]);
        } finally {
            File::delete($testFile);
        }
    }
}
