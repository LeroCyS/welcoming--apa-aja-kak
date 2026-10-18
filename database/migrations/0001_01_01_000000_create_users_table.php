<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->text('address')->nullable();
            $table->string('timezone')->default('Asia/Makassar');
            $table->time('default_service_start')->default('07:00:00');
            $table->time('default_service_end')->default('15:00:00');
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('school_id')->constrained();
            $table->enum('role', ['student', 'counselor', 'admin'])->index();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('identifier')->unique();
            $table->string('password');
            $table->boolean('must_change_password')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('student_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('class_name');
            $table->unsignedTinyInteger('grade_level');
            $table->string('pin_hash')->nullable();
            $table->boolean('pin_enabled')->default(false);
            $table->unsignedTinyInteger('pin_failed_attempts')->default(0);
            $table->timestamp('pin_locked_until')->nullable();
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('mood_prompt_enabled')->default(true);
            $table->boolean('mood_signal_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('counselor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('nickname');
            $table->string('photo_path')->nullable();
            $table->enum('gender', ['female', 'male'])->nullable();
            $table->string('hobbies')->nullable();
            $table->string('favorite_food')->nullable();
            $table->text('intro_message')->nullable();
            $table->text('offline_auto_reply')->nullable();
            $table->boolean('is_online')->default(false);
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();
        });

        Schema::create('counselor_service_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('counselor_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->time('start_time');
            $table->time('end_time');
            $table->timestamps();
            $table->index(['counselor_id', 'day_of_week']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counselor_service_hours');
        Schema::dropIfExists('counselor_profiles');
        Schema::dropIfExists('student_profiles');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('schools');
    }
};
