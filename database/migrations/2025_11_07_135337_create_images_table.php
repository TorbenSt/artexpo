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
        Schema::create('images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exhibition_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['public', 'press']);
            $table->string('path'); // Resized-Pfad
            $table->string('original_path')->nullable(); // Nur für Press-Original
            $table->string('credits')->nullable();
            $table->boolean('visible')->default(true);
            $table->string('position')->nullable(); // z.B. 'StartSeiteSlide' – erweiterbar durch einfaches Hinzufügen neuer Strings
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
