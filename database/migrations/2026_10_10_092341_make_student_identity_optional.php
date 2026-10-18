<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Murid mendaftar sendiri dengan email + password atau Google, tanpa NISN/identitas sekolah.
 * Nama, kelas, dan tingkat diisi murid sendiri di profil (opsional).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            $table->string('identifier')->nullable()->change();
            $table->string('password')->nullable()->change();
            $table->string('google_id')->nullable()->unique()->after('email');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('class_name')->nullable()->change();
            $table->unsignedTinyInteger('grade_level')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->string('class_name')->nullable(false)->change();
            $table->unsignedTinyInteger('grade_level')->nullable(false)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn('google_id');
            $table->string('name')->nullable(false)->change();
            $table->string('identifier')->nullable(false)->change();
            $table->string('password')->nullable(false)->change();
        });
    }
};
