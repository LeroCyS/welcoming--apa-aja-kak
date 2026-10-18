<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Urgensi kini dinilai Guru BK, bukan dipilih murid. NULL = "Belum dinilai".
 * Cerita yang masih berjalan direset agar dinilai ulang guru, kecuali yang berasal dari tombol darurat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->enum('urgency', ['normal', 'important', 'urgent'])->nullable()->default(null)->change();
            $table->timestamp('urgency_assessed_at')->nullable()->after('urgency');
        });

        DB::table('conversations')->where('status', '!=', 'resolved')->where('is_emergency', false)->update(['urgency' => null]);
    }

    public function down(): void
    {
        DB::table('conversations')->whereNull('urgency')->update(['urgency' => 'normal']);

        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('urgency_assessed_at');
            $table->enum('urgency', ['normal', 'important', 'urgent'])->default('normal')->nullable(false)->change();
        });
    }
};
