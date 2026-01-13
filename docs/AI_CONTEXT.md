# RAG Fun Facts for Social Media Posts

## Projekt-Ziel
Generiere ansprechende Social-Media-Posts für Kunstausstellungen, die automatisch verifizierte Fun Facts über Künstler und Kunstwerke von erlaubten Quellen einbinden. 

**Kernregel:** Fun Facts nur aus RAG-Index → keine Halluzinationen, keine erfundenen Fakten.

## Architektur
- **SQLite + FTS5:** Volumetrisch optimiert für Chunks (800–1200 Zeichen)
- **Laravel Queue:** `ChunkRagDocumentJob` asynchron verarbeitet
- **Source Whitelist:** Nur `rag_sources.is_allowed = true` werden genutzt
- **Logging:** Query → Chunks → Post mit Provenance tracking

## Erlaubte Quellen (Whitelist)
- Wikipedia (en, de): Künstler, Kunstgeschichte, Epochen
- Wikidata: strukturierte Daten, Cross-referencing
- Museum-APIs: Z.B. Metropolitan Museum API (public domain)
- Institutionelle Sammlungen: Z.B. artworks.zib.ch (mit Lizenz)
- Creative Commons Sammlungen: Z.B. Wikimedia Commons

**Nicht erlaubt:**
- Generische KI-Ausgaben (ohne Quellenangabe)
- Beliebige Blogs/Social-Media Posts
- Urheberrechtlich geschützte Inhalte ohne Lizenz

## Sprachen
- Default: `de` (Deutsch)
- Indexiert: `de`, `en`
- Fallback: Englische Facts wenn keine deutschen verfügbar

## Best Practices
1. **Source Attribution:** Jeder Fact muss Quelle und URL enthalten
2. **Validation:** Regex/Bounds checks auf Chunk-Länge
3. **Freshness:** `retrieved_at` optional für Crawler-Daten
4. **Performance:** FTS5 MATCH queries mit LIMIT + Pagination
5. **Testing:** Unerlaubte Quellen dürfen nie in Results erscheinen
