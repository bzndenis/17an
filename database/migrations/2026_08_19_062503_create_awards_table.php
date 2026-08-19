<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('awards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('position');
            $table->string('prize')->nullable();
            $table->timestamps();

            $table->unique(['competition_id', 'position']);
            $table->index(['competition_id', 'participant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('awards');
    }
};
