<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('datetime');
            $table->string('location')->nullable();
            $table->timestamps();

            $table->index(['event_id', 'datetime']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schedules');
    }
};
