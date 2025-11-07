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
        Schema::create('exhibitions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subtitle')->nullable();
            $table->text('intro_text')->nullable();
            $table->text('text')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('artist')->nullable();
            $table->string('program_booklet')->nullable(); // URL oder Pfad
            $table->string('program_booklet_cover')->nullable(); // Coverbild für Programmbooklet
            $table->string('flyer')->nullable();
            $table->string('flyer_cover')->nullable(); // Coverbild für Flyer
            $table->string('creative_booklet')->nullable();
            $table->string('creative_booklet_cover')->nullable(); // Coverbild für Creative Booklet
            $table->string('ticket_link')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exhibitions');
    }
};
