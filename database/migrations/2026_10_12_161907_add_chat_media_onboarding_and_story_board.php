<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pembaruan Oktober 2026:
 * - chat: pesan foto & lagu (link YouTube/Spotify);
 * - surat izin dihapus (kolom permit_code);
 * - murid memilih sekolah sendiri tanpa kode bergabung (kolom join_code dihapus);
 * - onboarding & walkthrough murid baru;
 * - "Bagikan ceritamu" menjadi papan Cerita Teman antar murid satu sekolah (menggantikan testimoni);
 * - komentar sesama Guru BK pada artikel Self-Help.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE messages MODIFY type ENUM('text', 'voice', 'image', 'song', 'article', 'session_invite', 'identity_request', 'mood_share', 'follow_up', 'auto_reply', 'emergency_info', 'system') NOT NULL");

        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->dropUnique(['permit_code']);
            $table->dropColumn('permit_code');
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropUnique(['join_code']);
            $table->dropColumn('join_code');
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->timestamp('onboarded_at')->nullable()->after('mood_signal_enabled');
            $table->timestamp('tour_completed_at')->nullable()->after('onboarded_at');
        });
        // Murid lama yang sudah punya sekolah tidak perlu mengulang onboarding.
        DB::table('student_profiles')->whereIn('user_id', DB::table('users')->whereNotNull('school_id')->select('id'))
            ->update(['onboarded_at' => now(), 'tour_completed_at' => now()]);

        Schema::dropIfExists('testimonials');

        Schema::create('story_posts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained()->cascadeOnDelete();
            // Penulis disimpan sebagai HMAC (seperti percakapan): tidak bisa dilacak ke akun oleh siapa pun.
            $table->char('author_hash', 64)->index();
            $table->string('display_label');
            $table->text('body');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->string('reject_reason')->nullable();
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
            $table->index(['school_id', 'status', 'moderated_at']);
        });

        Schema::create('story_reactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('story_post_id')->constrained()->cascadeOnDelete();
            $table->char('reactor_hash', 64);
            $table->enum('type', ['peluk', 'kuat', 'sama']);
            $table->timestamps();
            $table->unique(['story_post_id', 'reactor_hash', 'type']);
        });

        Schema::create('article_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_comments');
        Schema::dropIfExists('story_reactions');
        Schema::dropIfExists('story_posts');

        Schema::create('testimonials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('body', 300);
            $table->string('display_label');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->timestamps();
        });

        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn(['onboarded_at', 'tour_completed_at']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->string('join_code', 16)->nullable()->unique()->after('level');
        });

        Schema::table('counseling_sessions', function (Blueprint $table) {
            $table->string('permit_code', 10)->nullable()->unique()->after('status');
        });

        DB::table('messages')->whereIn('type', ['image', 'song'])->update(['type' => 'system']);
        DB::statement("ALTER TABLE messages MODIFY type ENUM('text', 'voice', 'article', 'session_invite', 'identity_request', 'mood_share', 'follow_up', 'auto_reply', 'emergency_info', 'system') NOT NULL");
    }
};
