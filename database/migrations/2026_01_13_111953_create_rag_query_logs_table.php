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
        Schema::create('rag_query_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_id')->nullable()->constrained('images')->nullOnDelete();
            $table->string('network'); // instagram, facebook, twitter
            $table->text('query_text'); // The search query
            $table->json('top_chunks'); // Array of retrieved chunks with metadata
            $table->integer('chunks_found'); // Count of chunks returned
            $table->timestamps();

            $table->index(['image_id', 'network']);
            $table->index(['created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rag_query_logs');
    }
};
