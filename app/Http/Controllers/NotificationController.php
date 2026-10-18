<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Services\ChatPresenter;
use App\Support\NotificationKind;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Blade;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $filter = $request->query('tampil') === 'belum' ? 'belum' : 'semua';
        $query = $filter === 'belum' ? $user->unreadNotifications() : $user->notifications();
        $notifications = $query->paginate(20)->withQueryString();

        return view('notifications.index', [
            'notifications' => $notifications,
            // Dikelompokkan per hari agar mudah dipindai: Hari ini, Kemarin, lalu tanggal.
            'groups' => $notifications->getCollection()->groupBy(fn (DatabaseNotification $n) => $this->dayLabel($n)),
            'filter' => $filter,
            'unread' => $user->unreadNotifications()->count(),
            'layout' => ['student' => 'layouts.student', 'counselor' => 'layouts.counselor', 'admin' => 'layouts.admin'][$user->role],
        ]);
    }

    public function feed(Request $request, ChatPresenter $presenter): JsonResponse
    {
        $user = $request->user();
        // Lonceng ini dipanggil di setiap halaman: tandanya Welcoming penerima sedang terbuka.
        $presenter->markDelivered($user);
        $icons = [];

        return response()->json([
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->limit(12)->get()->map(function (DatabaseNotification $n) use (&$icons) {
                $kind = NotificationKind::for($n->data['kind'] ?? $n->type);
                $icons[$kind['icon']] ??= Blade::render('<x-icon :name="$name" size="18" />', ['name' => $kind['icon']]);

                return [
                    'id' => $n->id,
                    'kind' => $n->data['kind'] ?? $n->type,
                    'label' => $kind['label'],
                    'tone' => ($n->data['critical'] ?? false) ? 'danger' : $kind['tone'],
                    'icon' => $icons[$kind['icon']],
                    'title' => $n->data['title'] ?? 'Notifikasi',
                    'body' => $n->data['body'] ?? null,
                    'url' => route('notifications.open', $n->id),
                    'target' => $n->data['url'] ?? null,
                    'critical' => (bool) ($n->data['critical'] ?? false),
                    'read' => (bool) $n->read_at,
                    'time' => $n->created_at->diffForHumans(),
                    'created' => $n->created_at->getTimestamp(),
                ];
            }),
        ]);
    }

    public function open(Request $request, string $id): RedirectResponse
    {
        $n = $request->user()->notifications()->findOrFail($id);
        $n->markAsRead();
        $url = $n->data['url'] ?? '/';

        // Hanya izinkan redirect internal.
        return redirect()->to(str_starts_with($url, url('/')) ? $url : $request->user()->homeRoute());
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return $request->expectsJson() ? response()->json(['ok' => true]) : back()->with('status', 'Semua notifikasi ditandai sudah dibaca.');
    }

    protected function dayLabel(DatabaseNotification $n): string
    {
        $date = $n->created_at->timezone(School::current()->timezone);

        return match (true) {
            $date->isToday() => 'Hari ini',
            $date->isYesterday() => 'Kemarin',
            default => $date->translatedFormat('l, j F Y'),
        };
    }
}
