<?php

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionSystem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('category')->nullable();
            $table->string('system')->default(CompetitionSystem::Knockout->value);
            $table->string('status')->default(CompetitionStatus::Draft->value);
            $table->string('location')->nullable();
            $table->dateTime('start_at')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('prize_1')->nullable();
            $table->string('prize_2')->nullable();
            $table->string('prize_3')->nullable();
            $table->string('banner')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'slug']);
            $table->index(['event_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitions');
    }
};
