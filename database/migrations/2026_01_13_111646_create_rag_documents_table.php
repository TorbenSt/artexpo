<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('rag_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rag_source_id')->constrained('rag_sources')->cascadeOnDelete();
            $table->string('title'); // Document title
            $table->string('url')->nullable(); // Source URL
            $table->string('language')->default('de'); // Language code (de, en)
            $table->longText('raw_text'); // Full document text
            $table->string('checksum')->nullable(); // SHA256 for deduplication
            $table->timestamp('retrieved_at')->nullable(); // When crawled/imported
            $table->string('artist_name')->nullable(); // For quick filtering
            $table->string('artwork_title')->nullable(); // For quick filtering
            $table->string('exhibition_title')->nullable(); // For quick filtering
            $table->timestamps();

            $table->index(['rag_source_id', 'language']);
            $table->index(['artist_name', 'language']);
            $table->unique('checksum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rag_documents');
    }
};
