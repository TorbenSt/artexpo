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
        Schema::create('rag_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // e.g., 'Wikipedia (de)', 'Metropolitan Museum API'
            $table->string('base_url')->nullable(); // e.g., 'https://en.wikipedia.org/wiki/'
            $table->text('license_note')->nullable(); // License info for attribution
            $table->boolean('is_allowed')->default(true); // Whitelist flag
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rag_sources');
    }
};
