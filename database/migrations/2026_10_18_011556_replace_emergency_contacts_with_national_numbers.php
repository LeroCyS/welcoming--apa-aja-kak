<?php

use App\Actions\ProvisionSchoolAction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Kontak darurat semua sekolah diseragamkan ke nomor nasional (112, 115, 129, 118/119).
 * Kontak lama (termasuk placeholder) dihapus dan diganti daftar bawaan di ProvisionSchoolAction.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('emergency_contacts')->delete();

            $now = now();
            DB::table('schools')->orderBy('id')->pluck('id')->each(function (int $schoolId) use ($now) {
                DB::table('emergency_contacts')->insert(collect(ProvisionSchoolAction::DEFAULT_CONTACTS)->map(fn (array $contact, int $i) => [
                    'school_id' => $schoolId,
                    'name' => $contact[1],
                    'description' => $contact[2],
                    'phone' => $contact[0],
                    'whatsapp' => null,
                    'sort_order' => $i + 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all());
            });
        });
    }

    /** Kontak lama tidak disimpan, jadi tidak ada langkah mundur. */
    public function down(): void {}
};
