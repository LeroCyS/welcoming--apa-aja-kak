<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounselorServiceHour extends Model
{
    use HasFactory;

    public const DAY_NAMES = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];

    protected $fillable = ['counselor_id', 'day_of_week', 'start_time', 'end_time'];

    protected function casts(): array
    {
        return ['day_of_week' => 'integer'];
    }

    public function counselor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }

    public function label(): string
    {
        return substr($this->start_time, 0, 5).'-'.substr($this->end_time, 0, 5);
    }
}
