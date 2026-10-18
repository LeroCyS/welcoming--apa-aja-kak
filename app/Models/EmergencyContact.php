<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyContact extends Model
{
    use HasFactory;

    protected $fillable = ['school_id', 'name', 'description', 'phone', 'whatsapp', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** Nomor yang bisa ditelepon (hanya digit & +). Untuk beberapa nomor seperti "118 / 119", dipakai yang pertama. Null jika masih placeholder. */
    public static function dialable(?string $number): ?string
    {
        $clean = preg_replace('/[^0-9+]/', '', explode('/', (string) $number)[0]);

        return strlen(ltrim($clean, '+')) >= 3 ? $clean : null;
    }

    public function telUrl(): ?string
    {
        $n = static::dialable($this->phone);

        return $n ? 'tel:'.$n : null;
    }

    public function waUrl(): ?string
    {
        $n = static::dialable($this->whatsapp);
        if (! $n) {
            return null;
        }
        $n = ltrim($n, '+');
        if (str_starts_with($n, '0')) {
            $n = '62'.substr($n, 1);
        }

        return 'https://wa.me/'.$n;
    }
}
