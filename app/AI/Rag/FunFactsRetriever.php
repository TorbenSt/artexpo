<?php

namespace App\AI\Rag;

use App\Models\RagQueryLog;
use Illuminate\Support\Facades\DB;

/**
 * FunFactsRetriever
 *
 * Retrieves fun facts from RAG index using FTS5 full-text search.
 * Only returns chunks from allowed sources.
 */
class FunFactsRetriever
{
    private ?int $imageId = null;
    private ?string $network = null;

    /**
     * Set context for logging.
     */
    public function withContext(?int $imageId, ?string $network = null): self
    {
        $this->imageId = $imageId;
        $this->network = $network;
        return $this;
    }

    /**
     * Retrieve fun facts relevant to a query.
     *
     * @param string $query Search query
     * @param int $limit Maximum chunks to return
     * @param string $lang Language filter (de, en)
     * @return array<int, array{text: string, source: string, title: string, url: ?string}>
     */
    public function retrieve(string $query, int $limit = 6, string $lang = 'de'): array
    {
        // Sanitize query for FTS5
        $ftsQuery = $this->prepareFtsQuery($query);

        if (empty($ftsQuery)) {
            return [];
        }

        // Search in FTS5 table with language filter, then join to get URLs
        $results = DB::select('
            SELECT DISTINCT
                rc.chunk_text as text,
                rs.name as source,
                rd.title,
                rd.url
            FROM rag_chunks rc
            JOIN rag_documents rd ON rc.rag_document_id = rd.id
            JOIN rag_sources rs ON rd.rag_source_id = rs.id
            WHERE rc.rowid IN (
                SELECT rowid FROM rag_chunks_fts 
                WHERE chunk_text MATCH ?
            )
                AND rd.language = ?
                AND rs.is_allowed = 1
            LIMIT ?
        ', [
            $ftsQuery,
            $lang,
            $limit,
        ]);

        $formatted = array_map(function ($row) {
            return [
                'text' => $row->text,
                'source' => $row->source,
                'title' => $row->title,
                'url' => $row->url,
            ];
        }, $results);

        // Log query if context is set
        if ($this->imageId && $this->network) {
            $this->logQuery($query, $formatted);
        }

        return $formatted;
    }

    /**
     * Retrieve fun facts with fallback to English if no German results.
     *
     * @param string $query Search query
     * @param int $limit Maximum chunks to return
     * @return array<int, array{text: string, source: string, title: string, url: ?string}>
     */
    public function retrieveWithFallback(string $query, int $limit = 6): array
    {
        // Try German first
        $results = $this->retrieve($query, $limit, 'de');

        // If no results, try English
        if (empty($results)) {
            $results = $this->retrieve($query, $limit, 'en');
        }

        return $results;
    }

    /**
     * Log the query and results.
     */
    private function logQuery(string $query, array $results): void
    {
        RagQueryLog::create([
            'image_id' => $this->imageId,
            'network' => $this->network,
            'query_text' => $query,
            'top_chunks' => array_slice($results, 0, 5), // Store top 5 for reference
            'chunks_found' => count($results),
        ]);
    }

    /**
     * Prepare FTS5 query from natural language input.
     *
     * @param string $query Raw query string
     * @return string FTS5-compatible query
     */
    private function prepareFtsQuery(string $query): string
    {
        // Remove special FTS5 characters unless intentional
        $query = trim($query);

        // Split into words and join with OR for broader matching
        // Alternative: use AND for stricter matching
        $words = preg_split('/\s+/', $query, -1, PREG_SPLIT_NO_EMPTY);

        if (empty($words)) {
            return '';
        }

        // Use OR for broader results; can switch to AND for stricter
        return implode(' OR ', $words);
    }
}
