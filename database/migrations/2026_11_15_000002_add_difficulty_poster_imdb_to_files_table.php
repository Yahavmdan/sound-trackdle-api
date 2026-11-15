<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->string('difficulty', 10)->nullable()->index(); // easy | medium | hard
            $table->string('poster_url', 500)->nullable();
            $table->string('imdb_id', 15)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['difficulty']);
            $table->dropColumn(['difficulty', 'poster_url', 'imdb_id']);
        });
    }
};
