<?php

namespace App\Console\Commands;

use App\Jobs\ChunkRagDocumentJob;
use App\Models\RagDocument;
use App\Models\RagSource;
use Illuminate\Console\Command;

class RagImportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rag:import
                            {--source= : Source name (required)}
                            {--title= : Document title (required)}
                            {--language=de : Language code (de, en)}
                            {--file= : Path to file to import}
                            {--url= : Source URL}
                            {--artist= : Artist name}
                            {--artwork= : Artwork title}
                            {--exhibition= : Exhibition title}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import a document into RAG index and queue chunking';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourceName = $this->option('source');
        $title = $this->option('title');
        $filePath = $this->option('file');
        $url = $this->option('url');
        $language = $this->option('language') ?? 'de';
        $artist = $this->option('artist');
        $artwork = $this->option('artwork');
        $exhibition = $this->option('exhibition');

        if (!$sourceName) {
            $this->error('--source is required');
            return self::FAILURE;
        }

        if (!$title) {
            $this->error('--title is required');
            return self::FAILURE;
        }

        if (!$filePath && !$this->input->getStream()) {
            $this->error('Either --file or piped content is required');
            return self::FAILURE;
        }

        // Get or create source
        $source = RagSource::firstOrCreate(
            ['name' => $sourceName],
            ['is_allowed' => true]
        );

        // Read file or stdin
        if ($filePath) {
            if (!file_exists($filePath)) {
                $this->error("File not found: $filePath");
                return self::FAILURE;
            }
            $rawText = file_get_contents($filePath);
        } else {
            $rawText = file_get_contents('php://stdin');
        }

        if (empty($rawText)) {
            $this->error('No content to import');
            return self::FAILURE;
        }

        // Check for duplicates
        $checksum = RagDocument::generateChecksum($rawText);
        if (RagDocument::where('checksum', $checksum)->exists()) {
            $this->warn("Document already exists (checksum: $checksum)");
            return self::SUCCESS;
        }

        // Create document
        $document = RagDocument::create([
            'rag_source_id' => $source->id,
            'title' => $title,
            'url' => $url,
            'language' => $language,
            'raw_text' => $rawText,
            'checksum' => $checksum,
            'retrieved_at' => now(),
            'artist_name' => $artist,
            'artwork_title' => $artwork,
            'exhibition_title' => $exhibition,
        ]);

        // Queue chunking job
        ChunkRagDocumentJob::dispatch($document);

        $this->info("Document imported: {$document->title} (ID: {$document->id})");
        $this->info("Chunking job queued");

        return self::SUCCESS;
    }
}
