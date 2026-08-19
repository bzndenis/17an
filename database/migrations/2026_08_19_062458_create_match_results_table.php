<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->constrained('matches')->cascadeOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('participants')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->dateTime('finished_at')->nullable();
            $table->timestamps();

            $table->unique('match_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_results');
    }
};
