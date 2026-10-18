<?php

namespace App\Http\Controllers;

use App\Models\VoiceNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

/** Audio hanya diputar lewat route ini (disk private), untuk murid pemilik & guru yang di-assign. */
class VoiceNoteStreamController extends Controller
{
    public function __invoke(Request $request, VoiceNote $voiceNote): Response
    {
        $conversation = $voiceNote->conversation;
        abort_unless($request->user()->can('view', $conversation), 403);
        abort_if($voiceNote->audio_deleted_at || ! Storage::disk('local')->exists($voiceNote->file_path), 410, 'Audio sudah dihapus otomatis');

        return Storage::disk('local')->response($voiceNote->file_path, 'pesan-suara.wav', [
            'Content-Type' => 'audio/wav',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
