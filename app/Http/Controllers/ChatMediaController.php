<?php

namespace App\Http\Controllers;

use App\Actions\SendCounselorMessageAction;
use App\Actions\SendStudentMessageAction;
use App\Models\Conversation;
use App\Models\Message;
use App\Services\ChatMedia;
use App\Services\ChatPresenter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/** Foto di chat, untuk murid pemilik maupun guru yang menangani. (Lagu dipindah ke catatan mood & Cerita Teman.) */
class ChatMediaController extends Controller
{
    public function __construct(
        protected ChatMedia $media,
        protected ChatPresenter $presenter,
    ) {}

    public function storeImage(Request $request, Conversation $conversation): JsonResponse
    {
        $this->authorize('reply', $conversation);
        $data = $request->validate([
            'image' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:'.config('welcoming.chat_image_max_kb')],
            'caption' => ['nullable', 'string', 'max:1000'],
        ], [
            'image.required' => 'Pilih foto dulu, ya.',
            'image.uploaded' => 'Foto gagal diunggah, kemungkinan ukurannya terlalu besar untuk server. Coba foto lain atau perkecil dulu.',
            'image.image' => 'File harus berupa foto.',
            'image.mimes' => 'Format foto harus JPG, PNG, WEBP, atau GIF.',
            'image.max' => 'Ukuran foto maksimal '.(int) (config('welcoming.chat_image_max_kb') / 1024).' MB.',
        ]);

        $stored = $this->media->storeImage($request->file('image'), 'chat-images/'.$conversation->uuid);
        $caption = filled($data['caption'] ?? null) ? trim($data['caption']) : null;
        try {
            $this->send($request, $conversation, 'image', $caption, ['image' => $stored]);
        } catch (Throwable $e) {
            // Jangan tinggalkan foto privat tanpa pesan yang merujuknya.
            Storage::disk('local')->delete($stored['path']);
            throw $e;
        }

        return response()->json($this->presenter->payload($request, $conversation->fresh()));
    }

    /** Foto hanya bisa dibuka lewat route ini (disk private), oleh murid pemilik & guru yang menangani. */
    public function showImage(Request $request, Message $message): Response
    {
        abort_unless($message->type === 'image' && $request->user()->can('view', $message->conversation), 404);
        $path = $message->meta['image']['path'] ?? null;
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->response($path, 'foto.jpg', [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    protected function send(Request $request, Conversation $c, string $type, ?string $body, array $meta): void
    {
        $user = $request->user();
        $user->isCounselor()
            ? app(SendCounselorMessageAction::class)->execute($c, $user, $type, $body, $meta)
            : app(SendStudentMessageAction::class)->execute($c, $type, $body, $meta);
    }
}
