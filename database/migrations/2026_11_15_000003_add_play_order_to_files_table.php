<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->unsignedInteger('play_order')->nullable()->index();
        });

        DB::table('files')->orderBy('id')->each(function ($row) {
            DB::table('files')->where('id', $row->id)->update(['play_order' => random_int(1, 1_000_000_000)]);
        });
    }

    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropIndex(['play_order']);
            $table->dropColumn('play_order');
        });
    }
};
