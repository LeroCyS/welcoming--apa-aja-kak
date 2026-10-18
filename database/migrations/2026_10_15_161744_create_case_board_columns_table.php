<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom kanban buatan Guru BK sendiri (mis. "Tunggu wali kelas"). Hanya pengelompokan pribadi:
 * status kasus tidak berubah saat kartu dipindah ke kolom ini.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('case_board_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 40);
            $table->string('color', 20)->default('sand');
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->foreignId('board_column_id')->nullable()->after('status')->constrained('case_board_columns')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('conversations', fn (Blueprint $table) => $table->dropConstrainedForeignId('board_column_id'));
        Schema::dropIfExists('case_board_columns');
    }
};
