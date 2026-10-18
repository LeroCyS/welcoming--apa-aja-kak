<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensitiveKeyword extends Model
{
    use HasFactory;

    /** @var array<string, string> kode kategori => label untuk admin */
    public const CATEGORIES = [
        'self_harm' => 'Menyakiti diri',
        'violence' => 'Kekerasan',
        'sexual_abuse' => 'Pelecehan',
        'bullying' => 'Perundungan',
        'distress' => 'Tekanan berat',
        'family' => 'Keluarga',
    ];

    /** @var array<int, array{0: string, 1: string}> tingkat => [warna badge, label] */
    public const SEVERITIES = [1 => ['sand', 'Perhatian'], 2 => ['terracotta', 'Serius'], 3 => ['danger', 'Kritis']];

    protected $fillable = ['school_id', 'phrase', 'severity', 'category', 'is_active'];

    protected function casts(): array
    {
        return [
            'severity' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function categoryLabel(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }
}
