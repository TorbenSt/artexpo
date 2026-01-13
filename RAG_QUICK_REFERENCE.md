# RAG System Quick Reference

## Files Created

### Migrations
- `database/migrations/2026_01_13_111640_create_rag_sources_table.php`
- `database/migrations/2026_01_13_111646_create_rag_documents_table.php`
- `database/migrations/2026_01_13_111646_create_rag_chunks_table.php`
- `database/migrations/2026_01_13_111700_create_rag_chunks_fts_table.php` (FTS5 + triggers)
- `database/migrations/2026_01_13_111953_create_rag_query_logs_table.php`

### Models
- `app/Models/RagSource.php`
- `app/Models/RagDocument.php`
- `app/Models/RagChunk.php`
- `app/Models/RagQueryLog.php`

### Services
- `app/AI/Rag/FunFactsRetriever.php` (Main retrieval logic)

### Jobs & Commands
- `app/Jobs/ChunkRagDocumentJob.php` (Document chunking)
- `app/Console/Commands/RagImportCommand.php` (Import documents)

### Seeders
- `database/seeders/RagSourcesSeeder.php` (Whitelist initialization)

### Tests
- `tests/Feature/AI/Rag/FunFactsRetrieverTest.php`
- `tests/Feature/Jobs/ChunkRagDocumentJobTest.php`
- `tests/Feature/Console/Commands/RagImportCommandTest.php`

### Configuration
- `config/socialmedia.php` (Updated with limits, languages)
- `docs/AI_CONTEXT.md` (Project context & guidelines)

### Modified Files
- `app/Jobs/GenerateSocialMediaPostsJob.php` (Integrated RAG retrieval)

---

## Key Classes & Methods

### FunFactsRetriever
```php
$retriever = app(FunFactsRetriever::class);

// Basic retrieval
$facts = $retriever->retrieve($query, $limit = 6, $lang = 'de');

// With fallback
$facts = $retriever->retrieveWithFallback($query, $limit = 6);

// With logging
$facts = $retriever->withContext($image_id, $network)->retrieve(...);
```

**Returns:** `array[{text, source, title, url}]`

### ChunkRagDocumentJob
```php
ChunkRagDocumentJob::dispatch($document);
// or in a queue: php artisan queue:work
```

Chunks document into 1000-character pieces with 150-character overlap.

### RagImportCommand
```bash
php artisan rag:import --source=... --title=... --file=... [--language=de]
```

### RagSourcesSeeder
```bash
php artisan db:seed --class=RagSourcesSeeder
```

Initializes 5 trusted sources (Wikipedia, Wikidata, Met Museum, Wikimedia).

---

## Queries

### Get all documents from a source
```php
$docs = RagSource::where('name', 'Wikipedia (de)')
    ->first()
    ->documents()
    ->get();
```

### Find chunks by artist
```php
$chunks = RagDocument::where('artist_name', 'Picasso')
    ->with('chunks')
    ->get()
    ->pluck('chunks')
    ->flatten();
```

### Query logs for an image
```php
$logs = RagQueryLog::where('image_id', $image->id)
    ->orderBy('created_at', 'desc')
    ->get();
```

### Disable a source
```php
RagSource::where('name', 'Bad Source')
    ->update(['is_allowed' => false]);
```

### Count documents per source
```php
RagSource::with('documents')
    ->get()
    ->map(fn($s) => $s->name . ': ' . $s->documents->count());
```

---

## Common Tasks

### Import Wikipedia Article
```bash
# Download article
curl -s "https://de.wikipedia.org/wiki/Special:Export/Pablo_Picasso" \
    | grep -o '<text.*</text>' \
    | sed 's/<text.*>//;s/<\/text>//' > /tmp/picasso.txt

# Import
php artisan rag:import \
    --source="Wikipedia (de)" \
    --title="Pablo Picasso" \
    --file=/tmp/picasso.txt \
    --artist="Pablo Picasso" \
    --url="https://de.wikipedia.org/wiki/Pablo_Picasso"
```

### Rebuild FTS Index
```bash
# Drop and rebuild
php artisan migrate:refresh --path=database/migrations/2026_01_13_111700_create_rag_chunks_fts_table.php
```

### Monitor Queue Processing
```bash
# Terminal 1: Run queue worker
php artisan queue:work

# Terminal 2: Monitor
watch "php artisan queue:failed"
php artisan queue:retry all  # Retry failed jobs
```

### Audit Query Usage
```php
// Top artists searched
RagQueryLog::selectRaw('query_text, COUNT(*) as count')
    ->groupBy('query_text')
    ->orderByDesc('count')
    ->limit(10)
    ->get();

// Most useful sources
RagQueryLog::selectRaw('top_chunks, COUNT(*) as count')
    ->where('chunks_found', '>', 0)
    ->groupBy('top_chunks')
    ->get();
```

---

## Debug Commands

```bash
# Check database setup
php artisan tinker
> DB::table('rag_sources')->count()
> DB::table('rag_documents')->count()
> DB::table('rag_chunks')->count()

# Test FTS5 search directly
> DB::select("SELECT * FROM rag_chunks_fts WHERE chunk_text MATCH 'Picasso'")

# Test retriever
> $r = app(\App\AI\Rag\FunFactsRetriever::class)
> $r->retrieve('Picasso', 5, 'de')
```

---

## Performance Checks

```php
// Count chunks per document (should be 1-20 typically)
RagDocument::withCount('chunks')
    ->orderByDesc('chunks_count')
    ->limit(10)
    ->get();

// Find undersized documents (might indicate import issues)
RagDocument::select('id', 'title', DB::raw('LENGTH(raw_text) as text_length'))
    ->where(DB::raw('LENGTH(raw_text)'), '<', 500)
    ->get();

// Check FTS index size
DB::select("SELECT COUNT(*) FROM rag_chunks_fts")[0]
```

---

## Testing All Systems

```bash
# Unit tests
php artisan test tests/Feature/AI/Rag/FunFactsRetrieverTest.php

# Job tests
php artisan test tests/Feature/Jobs/ChunkRagDocumentJobTest.php

# Command tests
php artisan test tests/Feature/Console/Commands/RagImportCommandTest.php

# All RAG tests
php artisan test tests/Feature/AI/Rag/ \
                  tests/Feature/Jobs/ChunkRagDocumentJobTest.php \
                  tests/Feature/Console/Commands/RagImportCommandTest.php
```

---

## Environment Notes

- **Database**: SQLite (uses FTS5 extension)
- **Queue**: Configured in `.env` (default: sync)
- **Memory**: Chunks up to 1000 chars stored in DB (not in memory)
- **Languages**: German (de), English (en)
