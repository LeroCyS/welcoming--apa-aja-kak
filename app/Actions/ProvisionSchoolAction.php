<?php

namespace App\Actions;

use App\Models\EmergencyContact;
use App\Models\School;
use App\Models\SensitiveKeyword;
use App\Services\TextNormalizer;

/** Isi awal tiap sekolah baru: daftar kata kunci sensitif dan kontak darurat bawaan (bisa diubah admin sekolah). */
class ProvisionSchoolAction
{
    /** @var array<int, array<string, list<string>>> severity => kategori => frasa */
    public const DEFAULT_KEYWORDS = [
        3 => [
            'self_harm' => ['bunuh diri', 'pengen mati', 'mau mati aja', 'akhiri hidup', 'mengakhiri hidup', 'melukai diri', 'nyakitin diri', 'menyakiti diri', 'sayat tangan', 'gak mau hidup lagi', 'lebih baik mati'],
            'sexual_abuse' => ['dilecehkan', 'diperkosa', 'pelecehan seksual', 'dicabuli'],
            'violence' => ['disiksa'],
        ],
        2 => [
            'distress' => ['pengen ilang aja', 'capek hidup', 'gak ada gunanya', 'gak mau hidup', 'gak kuat lagi', 'putus asa', 'hidupku hancur'],
            'violence' => ['dipukul', 'diancam', 'ditampar', 'ditendang', 'kekerasan'],
            'family' => ['kabur dari rumah', 'diusir dari rumah'],
        ],
        1 => [
            'bullying' => ['dibully', 'diejek terus', 'dikucilkan', 'dirundung', 'dipalak', 'diolok olok'],
            'distress' => ['stres banget', 'gak bisa tidur', 'nangis terus', 'sendirian terus', 'gak punya teman'],
        ],
    ];

    /** @var list<array{0: string, 1: string, 2: string}> nomor, nama, keterangan; sama untuk semua sekolah */
    public const DEFAULT_CONTACTS = [
        ['112', 'Darurat terpadu', 'Berbagai keadaan darurat, termasuk ancaman keselamatan. Ketersediaan bergantung pada daerah.'],
        ['115', 'Basarnas', 'Pencarian dan pertolongan, misalnya orang hilang di gunung atau laut.'],
        ['129', 'SAPA 129', 'Pengaduan kekerasan terhadap perempuan dan anak.'],
        ['118 / 119', 'Ambulans / darurat medis', 'Kondisi medis yang membutuhkan pertolongan segera.'],
    ];

    public function __construct(protected TextNormalizer $normalizer) {}

    public function execute(School $school): void
    {
        foreach (self::DEFAULT_KEYWORDS as $severity => $byCategory) {
            foreach ($byCategory as $category => $phrases) {
                foreach ($phrases as $phrase) {
                    SensitiveKeyword::firstOrCreate(
                        ['school_id' => $school->id, 'phrase' => $this->normalizer->normalize($phrase)],
                        ['severity' => $severity, 'category' => $category, 'is_active' => true],
                    );
                }
            }
        }

        foreach (self::DEFAULT_CONTACTS as $i => [$phone, $name, $description]) {
            EmergencyContact::firstOrCreate(
                ['school_id' => $school->id, 'name' => $name],
                ['description' => $description, 'phone' => $phone, 'whatsapp' => null, 'sort_order' => $i + 1, 'is_active' => true],
            );
        }
    }
}
