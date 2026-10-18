<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Murid (via student_hash, bukan identitas) yang dikeluarkan dari sebuah sekolah karena laporan iseng. */
class SchoolBan extends Model
{
    protected $fillable = ['school_id', 'student_hash', 'banned_by'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }
}
