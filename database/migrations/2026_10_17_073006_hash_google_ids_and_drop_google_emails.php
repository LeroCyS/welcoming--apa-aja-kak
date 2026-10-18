<?php

use App\Http\Controllers\Auth\GoogleController;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Login Google kini hanya menerima ID unik akun (izin `openid`), disimpan sebagai HMAC.
 * Akun Google lama: ID mentah diganti hash-nya, dan email dihapus bila akun itu murni Google (tanpa password),
 * karena tanpa password email tidak dipakai untuk masuk. Akun email + password tetap menyimpan emailnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')->whereNotNull('google_id')->orderBy('id')->get(['id', 'google_id', 'password'])->each(function ($user) {
            // Sudah berupa hash (64 karakter heksadesimal): lewati agar migrasi aman bila dijalankan ulang.
            if (preg_match('/^[0-9a-f]{64}$/', $user->google_id)) {
                return;
            }
            $changes = ['google_id' => GoogleController::googleHash($user->google_id)];
            if ($user->password === null) {
                $changes['email'] = null;
            }
            DB::table('users')->where('id', $user->id)->update($changes);
        });
    }

    /** Hash tidak bisa dikembalikan ke ID Google asli, jadi tidak ada langkah mundur. */
    public function down(): void {}
};
