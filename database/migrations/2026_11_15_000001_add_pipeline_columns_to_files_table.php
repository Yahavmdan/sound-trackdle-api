<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->unsignedInteger('tmdb_id')->nullable()->unique();
            $table->string('source_url')->nullable();
            $table->decimal('clip_start', 8, 2)->nullable();
            $table->char('token', 32)->nullable();
        });

        DB::table('files')->where('file_path', '')->update(['file_path' => null]);

        DB::table('files')->whereNull('token')->orderBy('id')->each(function ($row) {
            DB::table('files')->where('id', $row->id)->update(['token' => Str::random(32)]);
        });

        Schema::table('files', function (Blueprint $table) {
            $table->unique('token');
            $table->dropUnique(['name']);
            $table->unique(['name', 'year']);
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropUnique(['name', 'year']);
            $table->unique('name');
            $table->dropUnique(['token']);
            $table->dropUnique(['tmdb_id']);
            $table->dropColumn(['tmdb_id', 'source_url', 'clip_start', 'token']);
        });
    }
};
