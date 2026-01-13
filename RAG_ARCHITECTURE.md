# RAG System Architecture Diagram

## Data Flow: Image → Social Media Post with Fun Facts

```
┌─────────────────────────────────────────────────────────────────────┐
│                                                                       │
│  SOCIAL MEDIA POST GENERATION WITH RAG                              │
│                                                                       │
└─────────────────────────────────────────────────────────────────────┘

                        ┌──────────────────┐
                        │   Exhibition     │
                        │   (with artist)  │
                        └────────┬─────────┘
                                 │
                    ┌────────────┴────────────┐
                    │                         │
                    ▼                         ▼
              ┌──────────────┐         ┌──────────────┐
              │   Image 1    │         │   Image 2    │
              │ for_social   │         │ for_social   │
              │ _media=true  │         │ _media=true  │
              └──────┬───────┘         └──────┬───────┘
                     │                        │
                     └────────────┬───────────┘
                                  │
                                  ▼
                    ┌─────────────────────────┐
                    │ GenerateSocialMediaPost │
                    │ Job (foreach network)   │
                    └────────────┬────────────┘
                                 │
                    ┌────────────┴──────────────┐
                    │                          │
                    ▼                          ▼
        ┌──────────────────────┐  ┌──────────────────────┐
        │ buildFunFactsQuery() │  │ buildPrompt()        │
        │ "Artist Title"       │  │ (system + user msg)  │
        │ "Exhibition"         │  │                      │
        └──────────┬───────────┘  └──────────────────────┘
                   │                          │
                   ▼                          │
        ┌──────────────────────┐             │
        │ FunFactsRetriever    │             │
        │ .withContext()       │             │
        │ .retrieve()          │             │
        │ .retrieveWithFallback│             │
        └──────────┬───────────┘             │
                   │                          │
                   ▼                          │
        ┌──────────────────────┐             │
        │ FTS5 Query           │             │
        │ "Monet" OR "Painting"│             │
        │ MATCH chunk_text     │             │
        └──────────┬───────────┘             │
                   │                          │
                   ▼                          │
        ┌──────────────────────┐             │
        │ Filter Results:      │             │
        │ - Only allowed src   │             │
        │ - Language match     │             │
        │ - LIMIT 6            │             │
        └──────────┬───────────┘             │
                   │                          │
                   ▼                          │
        ┌──────────────────────┐             │
        │ Log Query:           │             │
        │ rag_query_logs       │             │
        │ {image_id, network,  │             │
        │  query, chunks_found}│             │
        └──────────┬───────────┘             │
                   │                          │
                   └────────────┬─────────────┘
                                │
                                ▼
                    ┌──────────────────────┐
                    │ OpenAI API Call      │
                    │ + fun facts context  │
                    │ + character limits   │
                    └────────────┬─────────┘
                                 │
                                 ▼
                    ┌──────────────────────┐
                    │ enforceNetworkRules()│
                    │ - Truncate to limit  │
                    │ - Instagram: 2200    │
                    │ - Twitter: 280 ⭐    │
                    │ - Facebook: 63206    │
                    └────────────┬─────────┘
                                 │
                                 ▼
                    ┌──────────────────────┐
                    │ SocialMediaPost      │
                    │ (status: draft)      │
                    │ - content            │
                    │ - network            │
                    │ - image_id           │
                    └──────────────────────┘


───────────────────────────────────────────────────────────────────────
```

## Document Ingestion Pipeline

```
┌──────────────────────────────────────────────────────────┐
│                   DOCUMENT INGESTION                     │
└──────────────────────────────────────────────────────────┘

    ┌─────────────────┐
    │ Document Source │
    │ - File          │
    │ - Stdin         │
    │ - API           │
    └────────┬────────┘
             │
             ▼
    ┌─────────────────────────┐
    │ RagImportCommand        │
    │ --source=Wikipedia      │
    │ --title=Picasso         │
    │ --artist=P. Picasso     │
    │ --file=/path/file.txt   │
    └────────┬────────────────┘
             │
             ▼
    ┌─────────────────────────┐
    │ Read raw_text           │
    │ Calculate SHA256        │
    │ Check for duplicate     │
    └────────┬────────────────┘
             │
             ├─ Duplicate Found?
             │  └─> Skip with warning ⚠️
             │
             └─ New Document
                └─> Create RagDocument
                    - source_id
                    - title, url
                    - language (de/en)
                    - raw_text (longText)
                    - checksum (unique)
                    - metadata (artist, artwork, exhibition)
                    └─> Queue ChunkRagDocumentJob
                        │
                        ▼
                        Split into chunks
                        - 1000 char chunks
                        - 150 char overlap
                        - Preserve word boundaries
                        │
                        ├─ Chunk 1
                        ├─ Chunk 2
                        └─ Chunk N
                        │
                        ▼
                        Insert into rag_chunks
                        (chunk_index, chunk_text)
                        │
                        ▼
                        ⭐ Trigger: rag_chunks_ai
                        Insert into rag_chunks_fts
                        (chunk_text, doc_title, artist_name,
                         source_name, language)


───────────────────────────────────────────────────────────────────────
```

## Database Schema Relationships

```
┌──────────────────────┐
│   rag_sources        │
├──────────────────────┤
│ id (PK)              │
│ name (UNIQUE)        │
│ base_url             │
│ license_note         │
│ is_allowed (flag)    │ ◄── WHITELIST ENFORCEMENT
└──────────┬───────────┘
           │ hasMany
           ▼
┌──────────────────────────┐         ┌──────────────────────┐
│   rag_documents          │         │  rag_query_logs      │
├──────────────────────────┤         ├──────────────────────┤
│ id (PK)                  │         │ id (PK)              │
│ rag_source_id (FK)       │         │ image_id (FK)        │
│ title                    │         │ network              │
│ url                      │         │ query_text           │
│ language (de/en)         │         │ top_chunks (JSON)    │
│ raw_text (longText)      │         │ chunks_found (count) │
│ checksum (UNIQUE)        │         │ created_at           │
│ retrieved_at             │         └──────────────────────┘
│ artist_name (index)      │
│ artwork_title            │
│ exhibition_title         │
└──────────┬───────────────┘
           │ hasMany
           ▼
┌──────────────────────────┐
│   rag_chunks             │
├──────────────────────────┤
│ id (PK)                  │
│ rag_document_id (FK)     │
│ chunk_index (sequence)   │
│ chunk_text (longText)    │ ──┐
│ created_at               │   │
└──────────┬───────────────┘   │
           │ (automatic sync)   │
           ▼                    │
┌──────────────────────────┐   │
│ rag_chunks_fts (FTS5)    │ ◄─┘ (Trigger-synced)
├──────────────────────────┤
│ rowid (implicit)         │
│ chunk_text (indexed)     │
│ doc_title (unindexed)    │
│ artist_name (unindexed)  │
│ source_name (unindexed)  │
│ language (unindexed)     │
└──────────────────────────┘


───────────────────────────────────────────────────────────────────────
```

## FTS5 Search Flow

```
    User Query: "Picasso Cubism"
            │
            ▼
    prepareFtsQuery()
    Split & OR join
    "Picasso OR Cubism"
            │
            ▼
    ┌─────────────────────────────────┐
    │ FTS5 MATCH Query                │
    │ WHERE chunk_text MATCH          │
    │       "Picasso OR Cubism"       │
    │   AND language = 'de'           │
    │   AND rowid IN (                │
    │       SELECT rowid FROM         │
    │       rag_chunks WHERE          │
    │       rag_document_id IN (      │
    │           SELECT id FROM        │
    │           rag_documents WHERE   │
    │           rag_source_id IN (    │
    │               SELECT id FROM    │
    │               rag_sources WHERE │
    │               is_allowed = 1    │
    │           )                     │
    │       )                         │
    │   )                             │
    └─────────────┬───────────────────┘
                  │
        ┌─────────┴─────────┐
        │                   │
        ▼                   ▼
    Match Found        No Match
        │                  │
        ├─ Try English  ───┘
        │  (fallback)
        │
        ▼
    {text, source, title, url}
    {text, source, title, url}
    {text, source, title, url}
            │
            ▼
    Log to rag_query_logs
            │
            ▼
    Return to GenerateSocialMediaPostsJob


───────────────────────────────────────────────────────────────────────
```

## Class Relationships

```
App\Jobs\GenerateSocialMediaPostsJob
    │
    ├─ uses ──────────▶ App\AI\Rag\FunFactsRetriever
    │                  ├─ retrieve()
    │                  ├─ retrieveWithFallback()
    │                  └─ withContext()
    │
    ├─ creates ──────▶ App\Models\SocialMediaPost
    │
    └─ reads ────────▶ App\Models\Image
                       └─ has ─▶ Exhibition


App\Console\Commands\RagImportCommand
    │
    ├─ creates ──────▶ App\Models\RagDocument
    │                  └─ has ──▶ RagSource
    │
    └─ dispatches ───▶ App\Jobs\ChunkRagDocumentJob
                       └─ creates ──▶ App\Models\RagChunk


App\Models\RagSource
    └─ hasMany ──────▶ RagDocument
                       └─ hasMany ──▶ RagChunk


App\Models\RagQueryLog
    └─ belongsTo ───▶ Image
                      └─ has ──▶ Exhibition


───────────────────────────────────────────────────────────────────────
```

## Configuration & Rules

```
┌────────────────────────────────────────┐
│   config/socialmedia.php               │
├────────────────────────────────────────┤
│ networks:                              │
│  - instagram                           │
│  - facebook                            │
│  - twitter                             │
│                                        │
│ limits:                                │
│  - instagram: 2200 chars               │
│  - facebook: 63206 chars               │
│  - twitter: 280 chars ⭐ (strict)      │
│                                        │
│ default_language: de                   │
│                                        │
│ supported_languages:                   │
│  - de (German) ◄─ preferred            │
│  - en (English) ◄─ fallback            │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│   Core Rules                           │
├────────────────────────────────────────┤
│ 1. Only allowed sources:               │
│    is_allowed = 1                      │
│                                        │
│ 2. Language matching:                  │
│    German first, English fallback      │
│                                        │
│ 3. Character limits:                   │
│    Enforced via truncation             │
│                                        │
│ 4. Deduplication:                      │
│    SHA256(raw_text) = unique           │
│                                        │
│ 5. No hallucinations:                  │
│    Only facts from RAG index            │
│    No matches = generic post           │
└────────────────────────────────────────┘
```

---

## Deployment Checklist

```
□ Run migrations: php artisan migrate
□ Seed sources: php artisan db:seed --class=RagSourcesSeeder
□ Import documents: php artisan rag:import ...
□ Configure queue: set QUEUE_CONNECTION in .env
□ Run tests: php artisan test tests/Feature/AI/Rag/ ...
□ Start queue worker: php artisan queue:work
□ Monitor logs: storage/logs/laravel.log
□ Check rag_query_logs table for usage
```

---

## Key Metrics & Thresholds

| Metric | Target | Note |
|--------|--------|------|
| FTS5 query time | <5ms | Indexed, O(log N) |
| Chunk size | 800-1200 chars | Overlap 150 chars |
| Query timeout | 30s | (for long imports) |
| Max chunks per query | 6 | (configurable) |
| Source limit | Unlimited | (enforced at query) |
| Document dedup | 100% | (SHA256 unique) |

