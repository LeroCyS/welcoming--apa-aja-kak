<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Lagu (link YouTube/Spotify) kini ditempel di catatan mood dan Cerita Teman, bukan di chat. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mood_entries', function (Blueprint $table) {
            $table->json('song')->nullable()->after('note');
        });
        Schema::table('story_posts', function (Blueprint $table) {
            $table->json('song')->nullable()->after('body');
        });
    }

    public function down(): void
    {
        Schema::table('story_posts', fn (Blueprint $table) => $table->dropColumn('song'));
        Schema::table('mood_entries', fn (Blueprint $table) => $table->dropColumn('song'));
    }
};
