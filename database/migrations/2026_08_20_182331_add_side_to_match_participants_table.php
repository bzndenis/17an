<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('match_participants', function (Blueprint $table) {
            $table->unsignedTinyInteger('side')->default(1)->after('participant_id');
            $table->index(['match_id', 'side']);
        });
    }

    public function down(): void
    {
        Schema::table('match_participants', function (Blueprint $table) {
            $table->dropIndex(['match_id', 'side']);
            $table->dropColumn('side');
        });
    }
};
