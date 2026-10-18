<?php

namespace App\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    use AuthorizesRequests;

    /** Data milik sekolah lain diperlakukan seolah tidak ada (404), agar keberadaannya pun tidak bocor. */
    protected function ensureSameSchool(Model $model): void
    {
        abort_unless((int) $model->getAttribute('school_id') === (int) Auth::user()?->school_id, 404);
    }
}
