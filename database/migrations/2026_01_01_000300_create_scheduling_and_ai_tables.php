<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_slots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('start_at');
            $table->dateTime('end_at');
            $table->string('location')->default('Ruang BK');
            $table->enum('status', ['available', 'booked', 'blocked'])->default('available');
            $table->timestamps();
            $table->index(['counselor_id', 'start_at']);
        });

        Schema::create('session_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counselor_id')->constrained('users');
            $table->string('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'declined', 'expired'])->default('pending');
            $table->dateTime('expires_at');
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('invitation_slot', function (Blueprint $table) {
            $table->foreignId('session_invitation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('counseling_slot_id')->constrained()->cascadeOnDelete();
            $table->primary(['session_invitation_id', 'counseling_slot_id']);
        });

        Schema::create('counseling_sessions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('session_invitation_id')->constrained();
            $table->foreignId('counseling_slot_id')->constrained();
            $table->foreignId('counselor_id')->constrained('users');
            $table->foreignId('student_id')->constrained('users');
            $table->enum('status', ['scheduled', 'completed', 'cancelled', 'no_show'])->default('scheduled');
            $table->string('permit_code', 10)->unique();
            $table->unsignedTinyInteger('reschedule_count')->default(0);
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_at');
            $table->dateTime('sent_at')->nullable();
            $table->enum('response', ['better', 'same', 'worse'])->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->timestamps();
        });

        Schema::create('conversation_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->enum('status', ['pending', 'done', 'failed'])->default('pending');
            $table->text('content')->nullable();
            $table->foreignId('last_message_id')->nullable()->constrained('messages')->nullOnDelete();
            $table->unsignedInteger('message_count')->default(0);
            $table->string('driver');
            $table->string('error')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['conversation_summaries', 'follow_ups', 'counseling_sessions', 'invitation_slot', 'session_invitations', 'counseling_slots'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
