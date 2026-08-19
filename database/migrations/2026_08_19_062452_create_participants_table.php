<?php

use App\Enums\ParticipantStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('participant_categories')->nullOnDelete();
            $table->string('name');
            $table->unsignedInteger('number');
            $table->string('gender', 10);
            $table->date('dob')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('rt_rw')->nullable();
            $table->string('team')->nullable();
            $table->string('status')->default(ParticipantStatus::Active->value);
            $table->string('photo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['event_id', 'number']);
            $table->index(['event_id', 'status']);
            $table->index(['event_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participants');
    }
};
