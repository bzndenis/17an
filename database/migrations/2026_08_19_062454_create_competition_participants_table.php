<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competition_participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->foreignId('participant_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('seed')->nullable();
            $table->timestamps();

            $table->unique(['competition_id', 'participant_id']);
            $table->index(['competition_id', 'seed']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competition_participants');
    }
};
