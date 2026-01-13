<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // FTS5 virtual table for full-text search on chunks
        // Store chunk_id for joins
        DB::statement('
            CREATE VIRTUAL TABLE rag_chunks_fts USING fts5(
                chunk_text,
                doc_title UNINDEXED,
                artist_name UNINDEXED,
                source_name UNINDEXED,
                language UNINDEXED
            )
        ');

        // We'll populate and sync this table via triggers on rag_chunks
        // Trigger: sync FTS on INSERT to rag_chunks
        DB::statement('
            CREATE TRIGGER rag_chunks_ai AFTER INSERT ON rag_chunks BEGIN
                INSERT INTO rag_chunks_fts(chunk_text, doc_title, artist_name, source_name, language)
                SELECT 
                    new.chunk_text,
                    rd.title,
                    rd.artist_name,
                    rs.name,
                    rd.language
                FROM rag_documents rd
                JOIN rag_sources rs ON rd.rag_source_id = rs.id
                WHERE rd.id = new.rag_document_id;
            END
        ');

        // Trigger: sync FTS on UPDATE to rag_chunks
        DB::statement('
            CREATE TRIGGER rag_chunks_au AFTER UPDATE ON rag_chunks BEGIN
                INSERT INTO rag_chunks_fts(rag_chunks_fts, chunk_text, doc_title, artist_name, source_name, language)
                SELECT 
                    \'delete\',
                    old.chunk_text,
                    rd.title,
                    rd.artist_name,
                    rs.name,
                    rd.language
                FROM rag_documents rd
                JOIN rag_sources rs ON rd.rag_source_id = rs.id
                WHERE rd.id = old.rag_document_id;
                
                INSERT INTO rag_chunks_fts(chunk_text, doc_title, artist_name, source_name, language)
                SELECT 
                    new.chunk_text,
                    rd.title,
                    rd.artist_name,
                    rs.name,
                    rd.language
                FROM rag_documents rd
                JOIN rag_sources rs ON rd.rag_source_id = rs.id
                WHERE rd.id = new.rag_document_id;
            END
        ');

        // Trigger: sync FTS on DELETE from rag_chunks
        DB::statement('
            CREATE TRIGGER rag_chunks_ad AFTER DELETE ON rag_chunks BEGIN
                INSERT INTO rag_chunks_fts(rag_chunks_fts, chunk_text, doc_title, artist_name, source_name, language)
                SELECT 
                    \'delete\',
                    old.chunk_text,
                    rd.title,
                    rd.artist_name,
                    rs.name,
                    rd.language
                FROM rag_documents rd
                JOIN rag_sources rs ON rd.rag_source_id = rs.id
                WHERE rd.id = old.rag_document_id;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP TRIGGER IF EXISTS rag_chunks_ai');
        DB::statement('DROP TRIGGER IF EXISTS rag_chunks_au');
        DB::statement('DROP TRIGGER IF EXISTS rag_chunks_ad');
        DB::statement('DROP TABLE IF EXISTS rag_chunks_fts');
    }
};
