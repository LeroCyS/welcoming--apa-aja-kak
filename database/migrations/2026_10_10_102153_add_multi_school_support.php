<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Welcoming bisa dipakai banyak sekolah: sekolah punya jenjang & kode bergabung,
 * murid memilih sekolahnya sendiri, dan konten (artikel, testimoni, kata kunci) terpisah per sekolah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('schools', function (Blueprint $table) {
            $table->enum('level', ['sd', 'smp', 'sma', 'smk'])->default('sma')->after('name');
            $table->string('join_code', 16)->nullable()->unique()->after('level');
        });
        foreach (DB::table('schools')->pluck('id') as $id) {
            DB::table('schools')->where('id', $id)->update(['join_code' => Str::upper(Str::random(8))]);
        }

        // Murid baru belum punya sekolah sampai memilih sendiri (jenjang → sekolah → kode).
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('school_id')->nullable()->change();
            $table->timestamp('school_joined_at')->nullable()->after('school_id');
        });
        DB::table('users')->whereNotNull('school_id')->update(['school_joined_at' => DB::raw('created_at')]);

        // Murid yang dikeluarkan dari sekolah karena laporan iseng tidak bisa bergabung lagi ke sekolah itu.
        Schema::create('school_bans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            $table->char('student_hash', 64);
            $table->foreignId('banned_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['school_id', 'student_hash']);
        });

        Schema::table('conversations', function (Blueprint $table) {
            $table->boolean('from_new_member')->default(false)->after('is_emergency');
            $table->timestamp('student_left_at')->nullable()->after('from_new_member');
            $table->timestamp('prank_reported_at')->nullable()->after('student_left_at');
            $table->foreignId('prank_reported_by')->nullable()->after('prank_reported_at')->constrained('users')->nullOnDelete();
        });

        $firstSchool = DB::table('schools')->orderBy('id')->value('id');
        foreach (['articles', 'testimonials', 'sensitive_keywords'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->foreignId('school_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            });
            if ($firstSchool) {
                DB::table($name)->update(['school_id' => $firstSchool]);
            }
        }
    }

    public function down(): void
    {
        foreach (['sensitive_keywords', 'testimonials', 'articles'] as $name) {
            Schema::table($name, function (Blueprint $table) {
                $table->dropConstrainedForeignId('school_id');
            });
        }

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('prank_reported_by');
            $table->dropColumn(['from_new_member', 'student_left_at', 'prank_reported_at']);
        });

        Schema::dropIfExists('school_bans');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('school_joined_at');
            $table->foreignId('school_id')->nullable(false)->change();
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['join_code']);
            $table->dropColumn(['level', 'join_code']);
        });
    }
};
