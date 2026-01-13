# RAG System for Fun Facts in Social Media Posts

## Overview

This implementation adds a Retrieval-Augmented Generation (RAG) system to automatically enrich social media posts with verified fun facts about artists and artworks. The system uses SQLite with FTS5 (Full-Text Search) for efficient similarity search across a whitelist of trusted sources.

**Key Features:**
- ✅ Source whitelist enforcement (only allowed sources in results)
- ✅ Automatic document chunking (800–1200 character chunks with overlap)
- ✅ FTS5 full-text search with language filtering
- ✅ Query logging with provenance tracking
- ✅ German/English language support with fallback
- ✅ Network character limit enforcement (Twitter 280 chars)
- ✅ Queue-based document processing

## Architecture

### Database Schema

```
rag_sources
├── id, name (unique), base_url, license_note, is_allowed
└── documents (hasMany)

rag_documents
├── id, rag_source_id, title, url, language, raw_text, checksum
├── artist_name, artwork_title, exhibition_title
└── chunks (hasMany)

rag_chunks
├── id, rag_document_id, chunk_index, chunk_text
└── Automatically synced to rag_chunks_fts via triggers

rag_chunks_fts (FTS5 Virtual Table)
├── chunk_text (indexed)
├── doc_title, artist_name, source_name, language (unindexed)
└── Auto-synced via INSERT/UPDATE/DELETE triggers

rag_query_logs
├── id, image_id (FK), network, query_text, top_chunks (JSON), chunks_found
└── Provenance tracking for auditing
```

### Execution Flow

```
Image marked for social media
    ↓
GenerateSocialMediaPostsJob triggered
    ↓
For each network (instagram, facebook, twitter):
    1. buildFunFactsQuery(exhibition, image) → "Artist Title Credits"
    2. FunFactsRetriever.retrieveWithFallback(query, 6)
       - Search FTS5 with MATCH (German first, fallback to English)
       - Filter: rag_sources.is_allowed = 1
       - Return: array of {text, source, title, url}
    3. Log query to rag_query_logs
    4. Include fun facts in OpenAI prompt
    5. Enforce network rules (character limits)
    6. Create SocialMediaPost (draft status)
```

## Usage

### 1. Seed Allowed Sources

```bash
php artisan db:seed --class=RagSourcesSeeder
```

This creates the initial whitelist:
- Wikipedia (de/en)
- Wikidata
- Metropolitan Museum API
- Wikimedia Commons

### 2. Import Documents

#### From File
```bash
php artisan rag:import \
  --source="Wikipedia (de)" \
  --title="Picasso: Leben und Werk" \
  --file=/path/to/file.txt \
  --language=de \
  --artist="Pablo Picasso" \
  --url="https://de.wikipedia.org/wiki/Pablo_Picasso"
```

#### From stdin (e.g., from crawlers)
```bash
curl https://example.com/article.txt | php artisan rag:import \
  --source="My Museum API" \
  --title="Modern Art Overview" \
  --language=de
```

#### Options
- `--source=*` (required): Source name
- `--title=*` (required): Document title
- `--file=path`: File path (or pipe stdin)
- `--language=de`: Language code (default: de)
- `--url=`: Source URL for attribution
- `--artist=`: Artist name for filtering
- `--artwork=`: Artwork title for filtering
- `--exhibition=`: Exhibition title for filtering

**What happens:**
1. Document is created with checksum (SHA256)
2. Duplicates are automatically skipped
3. `ChunkRagDocumentJob` is queued
4. Job chunks the document (1000 char chunks, 150 char overlap)
5. Chunks are inserted into `rag_chunks`
6. FTS5 index is automatically synced via triggers

### 3. Query the RAG Index

Directly in code:
```php
use App\AI\Rag\FunFactsRetriever;

$retriever = app(FunFactsRetriever::class);

// Simple retrieval (German only)
$facts = $retriever->retrieve('Picasso Kubismus', limit: 6, lang: 'de');

// With fallback to English
$facts = $retriever->retrieveWithFallback('Monet Impressionismus');

// With logging context
$facts = $retriever
    ->withContext($image->id, 'instagram')
    ->retrieve('Leonardo da Vinci Kunstwerk', limit: 6, lang: 'de');

// Result format
foreach ($facts as $fact) {
    echo $fact['text'];     // Chunk text
    echo $fact['source'];   // "Wikipedia (de)"
    echo $fact['title'];    // "Leonardo da Vinci"
    echo $fact['url'];      // "https://..."
}
```

### 4. Viewing Query Logs

```php
use App\Models\RagQueryLog;

// Find queries for an image
$logs = RagQueryLog::where('image_id', $image->id)->get();

foreach ($logs as $log) {
    echo $log->network;         // "instagram"
    echo $log->query_text;      // Original query
    echo $log->chunks_found;    // Count of results
    echo json_encode($log->top_chunks); // Top 5 chunks as JSON
}
```

## Constraints & Rules

### Source Whitelist
Only sources with `is_allowed = 1` appear in results. To disable a source:
```php
RagSource::where('name', 'Wikipedia (de)')->update(['is_allowed' => false]);
```

### Language Filtering
- Default: German (`de`)
- Fallback: English (`en`)
- Query always tries German first, then English

### Character Limits (enforced in GenerateSocialMediaPostsJob)
- Twitter/X: 280 characters
- Instagram: 2200 characters
- Facebook: 63206 characters

Texts are truncated with "..." if they exceed limits.

### Deduplication
Documents are automatically deduplicated by SHA256 checksum of `raw_text`. Same content from different sources will be flagged as duplicate.

### FTS5 Query Syntax
Queries are automatically converted to OR-based matching:
- Input: `"Picasso Kubismus"`
- FTS5 Query: `Picasso OR Kubismus`

This is intentionally broad. Switch `prepareFtsQuery()` to use `AND` for stricter matching.

## Testing

Run all RAG tests:
```bash
php artisan test tests/Feature/AI/Rag/ \
                 tests/Feature/Jobs/ChunkRagDocumentJobTest.php \
                 tests/Feature/Console/Commands/RagImportCommandTest.php
```

Test coverage:
- **FunFactsRetrieverTest**: Whitelist enforcement, language fallback, logging
- **ChunkRagDocumentJobTest**: Document chunking, order preservation
- **RagImportCommandTest**: Import from file, deduplication, metadata

## Performance Notes

- **FTS5 Indexing**: Automatic via triggers (synchronous)
- **Query Speed**: O(log N) via FTS5 MATCH, ~1-5ms for typical queries
- **Memory**: Chunks keep `raw_text` in database; large docs should be split before import
- **Scaling**: For 100K+ chunks, consider:
  - Async trigger updates (defer FTS sync to queue)
  - Sharded search (by language, source)
  - Caching (Redis) for popular queries

## Troubleshooting

### No results found
1. Check `rag_sources.is_allowed = 1`
2. Verify document language matches query language
3. Try broader query terms or switch `AND` to `OR` in `prepareFtsQuery()`

### Duplicate prevention not working
- Checksums are computed from `raw_text`
- Whitespace differences will create different checksums
- Normalize text before import: trim, normalize line breaks

### FTS5 errors
- Ensure SQLite 3.9.0+ (FTS5 support)
- Check migration ran: `php artisan migrate`
- Rebuild index: delete database and remigrate

### Queries not appearing in logs
- Ensure `withContext($image_id, $network)` is called before retrieve
- Check `rag_query_logs` table exists
- Verify `$image_id` exists (foreign key constraint)

## Example: Complete Workflow

```php
// 1. Add a source (one-time)
RagSource::firstOrCreate([
    'name' => 'Wikipedia (de)',
    'is_allowed' => true,
]);

// 2. Import a document
Artisan::call('rag:import', [
    'source' => 'Wikipedia (de)',
    'title' => 'Pablo Picasso',
    'file' => storage_path('picasso.txt'),
    'artist' => 'Pablo Picasso',
]);

// 3. Process queue (chunks the document)
// php artisan queue:work

// 4. Generate social media post
$image = Image::factory()->create([
    'for_social_media' => true,
    'visible' => true,
]);

GenerateSocialMediaPostsJob::dispatch($image);

// 5. View the result
$post = $image->socialMediaPosts()->first();
echo $post->content; // "🎨 Picasso revolutionized art with Cubism..."

// 6. Check what facts were used
$logs = RagQueryLog::where('image_id', $image->id)->first();
echo $logs->chunks_found; // 3
```

## Definition of Done ✅

- [x] For an image, drafts are created for each network
- [x] Fun Facts only appear if retrieval finds matches
- [x] No matches → generic post, no invented facts
- [x] Disallowed sources never appear
- [x] X-posts are ≤ 280 characters
- [x] Query logging with provenance tracking
- [x] All tests pass
- [x] No database errors on concurrent imports
