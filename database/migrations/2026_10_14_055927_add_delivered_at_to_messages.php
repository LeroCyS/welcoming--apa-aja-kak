<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Centang seperti WhatsApp: satu = terkirim (penerima offline), dua abu-abu = sudah sampai ke perangkat penerima
 * (Welcoming-nya sedang terbuka), dua hijau = dibaca.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->timestamp('delivered_at')->nullable()->after('meta');
        });
        // Pesan yang sudah dibaca pasti sudah sampai.
        DB::table('messages')->whereNotNull('read_at')->update(['delivered_at' => DB::raw('read_at')]);
    }

    public function down(): void
    {
        Schema::table('messages', fn (Blueprint $table) => $table->dropColumn('delivered_at'));
    }
};
