<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Balasan cepat seperti WhatsApp Business: ketik /shortcut + Enter di ruang chat untuk memakai template.
 * Template lama diberi shortcut dari judulnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reply_templates', function (Blueprint $table) {
            $table->string('shortcut', 30)->nullable()->after('title');
            $table->unique(['counselor_id', 'shortcut']);
        });

        $used = [];
        DB::table('reply_templates')->orderBy('id')->get(['id', 'counselor_id', 'title'])->each(function ($t) use (&$used) {
            $base = Str::limit(Str::slug($t->title, ''), 24, '') ?: 'template';
            $shortcut = $base;
            for ($i = 2; isset($used[$t->counselor_id ?? 0][$shortcut]); $i++) {
                $shortcut = $base.$i;
            }
            $used[$t->counselor_id ?? 0][$shortcut] = true;
            DB::table('reply_templates')->where('id', $t->id)->update(['shortcut' => $shortcut]);
        });
    }

    public function down(): void
    {
        Schema::table('reply_templates', function (Blueprint $table) {
            $table->dropUnique(['counselor_id', 'shortcut']);
            $table->dropColumn('shortcut');
        });
    }
};
