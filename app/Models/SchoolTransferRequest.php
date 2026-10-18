<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Permintaan murid agar dilepaskan dari sekolahnya. Admin hanya melihat "Murid #id" dan alasan pilihan
 * (bukan teks bebas, supaya identitas tidak ikut terbaca admin).
 */
class SchoolTransferRequest extends Model
{
    use HasFactory;

    /** @var array<string, string> */
    public const REASONS = [
        'moving' => 'Pindah ke sekolah lain',
        'wrong_school' => 'Salah pilih sekolah',
        'other' => 'Alasan lain',
    ];

    protected $fillable = ['student_id', 'school_id', 'reason', 'status', 'handled_by', 'handled_at'];

    protected function casts(): array
    {
        return [
            'handled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function reasonLabel(): string
    {
        return self::REASONS[$this->reason] ?? 'Alasan lain';
    }
}
