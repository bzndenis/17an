<?php

use App\Enums\MatchStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('round_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competition_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('match_number');
            $table->string('status')->default(MatchStatus::Scheduled->value);
            $table->dateTime('scheduled_at')->nullable();
            $table->string('venue')->nullable();
            $table->unsignedBigInteger('next_match_id')->nullable();
            $table->unsignedSmallInteger('bracket_position')->nullable();
            $table->timestamps();

            $table->unique(['competition_id', 'match_number']);
            $table->index(['competition_id', 'status']);
            $table->index('scheduled_at');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreign('next_match_id')->references('id')->on('matches')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
