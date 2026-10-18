<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Edit & hapus pesan seperti WhatsApp.
 * - edited_at: pesan teks diubah pengirimnya (maks. 15 menit).
 * - retracted_at: "hapus untuk semua orang" (maks. 1 jam, atau lewat "bersihkan chat untuk semua orang").
 * - original_body: teks asli pesan murid berkata kunci kritis yang diedit/dihapus, tetap terlihat oleh Guru BK.
 * - message_hides: "hapus untuk saya" / "bersihkan chat untuk saya", hanya menyembunyikan dari satu pengguna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('edited_at')->nullable()->after('read_at');
            $table->timestamp('retracted_at')->nullable()->after('edited_at');
            $table->text('original_body')->nullable()->after('body');
        });

        Schema::create('message_hides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_hides');
        Schema::table('messages', fn (Blueprint $table) => $table->dropColumn(['edited_at', 'retracted_at', 'original_body']));
    }
};
