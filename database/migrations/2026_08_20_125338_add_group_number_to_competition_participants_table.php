<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('competition_participants', function (Blueprint $table) {
            $table->unsignedTinyInteger('group_number')->nullable()->after('seed');
            $table->index(['competition_id', 'group_number']);
        });
    }

    public function down(): void
    {
        Schema::table('competition_participants', function (Blueprint $table) {
            $table->dropIndex(['competition_id', 'group_number']);
            $table->dropColumn('group_number');
        });
    }
};
