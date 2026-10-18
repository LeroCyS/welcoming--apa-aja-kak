<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topics', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('icon');
            $table->string('color');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('school_id')->constrained();
            $table->foreignId('counselor_id')->constrained('users');
            $table->text('student_ref');
            $table->char('student_hash', 64)->index();
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('grade_level');
            $table->boolean('is_anonymous')->default(true);
            $table->string('anonymous_alias');
            $table->string('anonymous_avatar');
            $table->timestamp('identity_revealed_at')->nullable();
            $table->foreignId('revealed_to_counselor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('urgency', ['normal', 'important', 'urgent'])->default('normal');
            $table->smallInteger('priority_score')->default(0);
            $table->enum('status', ['new', 'monitoring', 'referred_parent', 'resolved'])->default('new');
            $table->boolean('is_emergency')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamp('last_student_message_at')->nullable();
            $table->timestamp('last_counselor_message_at')->nullable();
            $table->timestamp('first_response_at')->nullable();
            $table->timestamp('last_auto_reply_at')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();
            $table->index(['counselor_id', 'status', 'priority_score']);
        });

        Schema::create('conversation_topic', function (Blueprint $table) {
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->primary(['conversation_id', 'topic_id']);
        });

        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender_type', ['student', 'counselor', 'system']);
            $table->foreignId('sender_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('type', ['text', 'voice', 'article', 'session_invite', 'identity_request', 'mood_share', 'follow_up', 'auto_reply', 'emergency_info', 'system']);
            $table->text('body')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['conversation_id', 'id']);
        });

        Schema::create('voice_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->string('file_path');
            $table->string('mime_type')->default('audio/wav');
            $table->unsignedInteger('size_bytes');
            $table->unsignedSmallInteger('duration_seconds');
            $table->enum('disguise_mode', ['none', 'deep', 'high', 'robot'])->default('deep');
            $table->text('transcript')->nullable();
            $table->enum('transcript_status', ['pending', 'done', 'failed', 'skipped'])->default('pending');
            $table->timestamp('audio_deleted_at')->nullable();
            $table->timestamps();
        });

        Schema::create('identity_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users');
            $table->string('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sensitive_keywords', function (Blueprint $table) {
            $table->id();
            $table->string('phrase');
            $table->unsignedTinyInteger('severity');
            $table->string('category');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('conversation_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('message_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('sensitive_keyword_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('severity');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('case_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users');
            $table->text('body');
            $table->timestamps();
        });

        Schema::create('case_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->string('note')->nullable();
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('case_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_counselor_id')->constrained('users');
            $table->foreignId('to_counselor_id')->constrained('users');
            $table->text('handover_note')->nullable();
            $table->timestamps();
        });

        Schema::create('reply_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselor_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->foreignId('topic_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['reply_templates', 'case_transfers', 'case_status_logs', 'case_notes', 'conversation_flags', 'sensitive_keywords', 'identity_requests', 'voice_notes', 'messages', 'conversation_topic', 'conversations', 'topics'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
