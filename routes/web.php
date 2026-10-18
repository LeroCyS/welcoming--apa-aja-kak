<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ChatMediaController;
use App\Http\Controllers\ChatMessageController;
use App\Http\Controllers\Counselor;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\PushSubscriptionController;
use App\Http\Controllers\QuickExitController;
use App\Http\Controllers\Student;
use App\Http\Controllers\VoiceNoteStreamController;
use Illuminate\Support\Facades\Route;

// ---------- Publik ----------
Route::get('/', [PublicController::class, 'landing'])->name('landing');
Route::get('/darurat', [PublicController::class, 'emergency'])->name('emergency');
Route::get('/kebijakan-privasi', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/syarat-ketentuan', [PublicController::class, 'terms'])->name('terms');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->middleware('throttle:login')->name('login.store');

    // Murid daftar sendiri: hanya email + password (atau Google), tanpa NISN. Sekolah dipilih saat onboarding.
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->middleware('throttle:register')->name('register.store');
    // Akun baru dibuat setelah email diverifikasi dengan kode OTP.
    Route::get('/daftar/verifikasi', [RegisterController::class, 'verifyForm'])->name('register.verify');
    Route::post('/daftar/verifikasi', [RegisterController::class, 'verify'])->middleware('throttle:register-otp')->name('register.verify.store');
    Route::post('/daftar/verifikasi/kirim-ulang', [RegisterController::class, 'resend'])->middleware('throttle:register-otp')->name('register.verify.resend');
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('auth.google.callback');

    Route::get('/lupa-password', [PasswordResetController::class, 'create'])->name('password.request');
    Route::post('/lupa-password', [PasswordResetController::class, 'store'])->middleware('throttle:password-reset')->name('password.email');
    Route::get('/reset-password/{token}', [PasswordResetController::class, 'edit'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('/keluar-cepat', QuickExitController::class)->name('quick-exit');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');
    Route::get('/ganti-password', [PasswordController::class, 'edit'])->name('password.change');
    Route::post('/ganti-password', [PasswordController::class, 'update'])->name('password.change.update');

    // Langganan notifikasi perangkat boleh diatur sejak onboarding (sebelum memilih sekolah).
    Route::post('/push/langganan', [PushSubscriptionController::class, 'store'])->middleware('password.changed')->name('push.subscribe');
    Route::delete('/push/langganan', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    Route::middleware(['password.changed', 'quick.unlocked'])->group(function () {
        Route::get('/notifikasi', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifikasi/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
        Route::post('/notifikasi/baca-semua', [NotificationController::class, 'readAll'])->name('notifications.read-all');
        Route::get('/notifikasi/{id}', [NotificationController::class, 'open'])->name('notifications.open');
        // Isi chat (suara & foto) ikut terkunci PIN bagi murid, sama seperti halaman ceritanya.
        Route::get('/voice-notes/{voiceNote}/stream', VoiceNoteStreamController::class)->middleware('pin.unlocked')->name('voice-notes.stream');
        Route::get('/foto-chat/{message}', [ChatMediaController::class, 'showImage'])->middleware('pin.unlocked')->name('chat-images.show');
    });
});

// ---------- Murid ----------
Route::prefix('murid')->name('student.')->middleware(['auth', 'role:student', 'password.changed', 'quick.unlocked', 'student.onboarded'])->group(function () {
    // Onboarding: tutorial → setup akun → jenjang → sekolah (tanpa kode, tidak bisa diganti sendiri) → Guru BK.
    Route::get('/mulai', [Student\OnboardingController::class, 'show'])->name('onboarding');
    Route::get('/mulai/guru', [Student\OnboardingController::class, 'counselors'])->name('onboarding.counselors');
    Route::post('/mulai', [Student\OnboardingController::class, 'store'])->name('onboarding.store');
    Route::post('/tur-selesai', [Student\OnboardingController::class, 'completeTour'])->name('tour.complete');

    Route::get('/', [Student\HomeController::class, 'index'])->name('home');
    Route::post('/checkin', [Student\MoodController::class, 'checkin'])->name('mood.checkin');
    Route::post('/ajakan-cerita', [Student\HomeController::class, 'respondPrompt'])->name('mood-prompt.respond');
    Route::get('/status-guru', [Student\CounselorController::class, 'statuses'])->name('counselors.statuses');

    Route::get('/self-help', [Student\SelfHelpController::class, 'index'])->name('selfhelp.index');
    Route::get('/self-help/{article:slug}', [Student\SelfHelpController::class, 'show'])->name('selfhelp.show');
    Route::get('/guru', [Student\CounselorController::class, 'index'])->name('counselors');

    Route::get('/profil', [Student\ProfileController::class, 'show'])->name('profile');
    Route::patch('/profil/pengaturan', [Student\ProfileController::class, 'settings'])->name('profile.settings');
    Route::patch('/profil/identitas', [Student\ProfileController::class, 'identity'])->name('profile.identity');
    Route::post('/profil/password', [Student\ProfileController::class, 'password'])->name('profile.password');
    Route::post('/profil/pindah-sekolah', [Student\SchoolTransferController::class, 'store'])->name('transfer.store');
    Route::delete('/profil/pindah-sekolah', [Student\SchoolTransferController::class, 'destroy'])->name('transfer.destroy');
    Route::post('/pin', [Student\PinController::class, 'enable'])->name('pin.enable');
    Route::delete('/pin', [Student\PinController::class, 'disable'])->name('pin.disable');
    Route::get('/kunci', [Student\PinController::class, 'lock'])->name('pin.lock');
    Route::post('/kunci', [Student\PinController::class, 'unlock'])->name('pin.unlock')->middleware('throttle:pin');
    Route::post('/kunci/lupa', [Student\PinController::class, 'forgot'])->name('pin.forgot')->middleware('throttle:pin-forgot');

    // Cerita Teman: cerita anonim antar murid satu sekolah, ditinjau Guru BK, hanya bisa diberi reaksi.
    Route::get('/cerita-teman', [Student\StoryController::class, 'index'])->name('stories.index');
    Route::get('/cerita-teman/tulis', [Student\StoryController::class, 'create'])->name('stories.create');
    Route::post('/cerita-teman', [Student\StoryController::class, 'store'])->middleware('throttle:stories')->name('stories.store');
    Route::post('/cerita-teman/{story}/reaksi', [Student\StoryController::class, 'react'])->middleware('throttle:reactions')->name('stories.react');
    Route::delete('/cerita-teman/{story}', [Student\StoryController::class, 'destroy'])->name('stories.destroy');

    // Darurat: tidak dikunci PIN
    Route::post('/cerita/darurat', [Student\ConversationController::class, 'emergency'])->name('conversations.emergency');

    Route::middleware('pin.unlocked')->group(function () {
        Route::get('/cerita', [Student\ConversationController::class, 'index'])->name('conversations.index');
        Route::get('/cerita/baru', [Student\ConversationController::class, 'create'])->name('conversations.create');
        Route::get('/cerita/feed', [Student\ConversationController::class, 'feed'])->name('conversations.feed');
        Route::post('/cerita/baru', [Student\ConversationController::class, 'store'])->name('conversations.store');
        Route::get('/cerita/{conversation}', [Student\ConversationController::class, 'show'])->name('conversations.show');
        Route::get('/cerita/{conversation}/messages', [Student\MessageController::class, 'index'])->name('conversations.messages.index');
        Route::post('/cerita/{conversation}/messages', [Student\MessageController::class, 'store'])->name('conversations.messages.store')->middleware('throttle:messages');
        Route::post('/cerita/{conversation}/voice', [Student\VoiceNoteController::class, 'store'])->name('conversations.voice.store')->middleware('throttle:voice');
        Route::post('/cerita/{conversation}/foto', [ChatMediaController::class, 'storeImage'])->name('conversations.image.store')->middleware('throttle:media');
        Route::post('/cerita/{conversation}/mengetik', [Student\MessageController::class, 'typing'])->name('conversations.typing')->middleware('throttle:typing');
        Route::patch('/cerita/{conversation}/pesan/{message}', [ChatMessageController::class, 'update'])->scopeBindings()->name('conversations.messages.update')->middleware('throttle:messages');
        Route::delete('/cerita/{conversation}/pesan/{message}', [ChatMessageController::class, 'destroy'])->scopeBindings()->name('conversations.messages.destroy');
        Route::post('/cerita/{conversation}/identitas', [Student\IdentityController::class, 'reveal'])->name('conversations.reveal');
        Route::post('/permintaan-identitas/{identityRequest}', [Student\IdentityController::class, 'respond'])->name('identity-requests.respond');
        Route::post('/undangan/{invitation}/terima', [Student\InvitationController::class, 'accept'])->name('invitations.accept');
        Route::post('/undangan/{invitation}/tolak', [Student\InvitationController::class, 'decline'])->name('invitations.decline');
        Route::post('/follow-up/{followUp}', [Student\FollowUpController::class, 'respond'])->name('follow-ups.respond');

        Route::get('/mood', [Student\MoodController::class, 'index'])->name('mood.index');
        Route::put('/mood', [Student\MoodController::class, 'upsert'])->name('mood.upsert');
        Route::post('/mood/{moodEntry}/bagikan', [Student\MoodController::class, 'share'])->name('mood.share');

        Route::get('/jadwal', [Student\ScheduleController::class, 'index'])->name('schedule.index');
        Route::get('/jadwal/{session}/slot', [Student\ScheduleController::class, 'slots'])->name('schedule.slots');
        Route::post('/jadwal/{session}/ubah', [Student\ScheduleController::class, 'reschedule'])->name('schedule.reschedule');
    });
});

// ---------- Guru BK ----------
Route::prefix('guru')->name('counselor.')->middleware(['auth', 'role:counselor', 'password.changed'])->group(function () {
    Route::get('/', [Counselor\DashboardController::class, 'index'])->name('dashboard');
    Route::post('/presence', [Counselor\PresenceController::class, 'heartbeat'])->name('presence.heartbeat');
    Route::post('/presence/toggle', [Counselor\PresenceController::class, 'toggle'])->name('presence.toggle');

    Route::get('/inbox', [Counselor\InboxController::class, 'index'])->name('inbox');
    Route::get('/inbox/feed', [Counselor\InboxController::class, 'feed'])->name('inbox.feed');
    Route::get('/inbox/{conversation}', [Counselor\ConversationController::class, 'show'])->name('conversations.show');
    Route::get('/inbox/{conversation}/messages', [Counselor\ConversationController::class, 'messages'])->name('conversations.messages.index');
    Route::post('/inbox/{conversation}/messages', [Counselor\ConversationController::class, 'sendMessage'])->name('conversations.messages.store')->middleware('throttle:messages');
    Route::post('/inbox/{conversation}/foto', [ChatMediaController::class, 'storeImage'])->name('conversations.image.store')->middleware('throttle:media');
    Route::post('/inbox/{conversation}/mengetik', [Counselor\ConversationController::class, 'typing'])->name('conversations.typing')->middleware('throttle:typing');
    Route::patch('/inbox/{conversation}/pesan/{message}', [ChatMessageController::class, 'update'])->scopeBindings()->name('conversations.messages.update')->middleware('throttle:messages');
    Route::delete('/inbox/{conversation}/pesan/{message}', [ChatMessageController::class, 'destroy'])->scopeBindings()->name('conversations.messages.destroy');
    Route::patch('/inbox/{conversation}/status', [Counselor\CaseController::class, 'updateStatus'])->name('conversations.status');
    Route::patch('/inbox/{conversation}/urgensi', [Counselor\CaseController::class, 'updateUrgency'])->name('conversations.urgency');
    Route::post('/inbox/{conversation}/transfer', [Counselor\CaseController::class, 'transfer'])->name('conversations.transfer');
    Route::post('/inbox/{conversation}/artikel', [Counselor\ConversationController::class, 'sendArticle'])->name('conversations.article');
    Route::post('/inbox/{conversation}/minta-identitas', [Counselor\ConversationController::class, 'requestIdentity'])->name('conversations.identity-request');
    Route::post('/inbox/{conversation}/undangan', [Counselor\InvitationController::class, 'store'])->name('conversations.invitations.store');
    Route::post('/inbox/{conversation}/catatan', [Counselor\CaseNoteController::class, 'store'])->name('notes.store');
    Route::patch('/catatan/{caseNote}', [Counselor\CaseNoteController::class, 'update'])->name('notes.update');
    Route::delete('/catatan/{caseNote}', [Counselor\CaseNoteController::class, 'destroy'])->name('notes.destroy');
    Route::post('/inbox/{conversation}/iseng', [Counselor\CaseController::class, 'togglePrank'])->name('conversations.prank');
    Route::post('/flag/{flag}/tinjau', [Counselor\CaseController::class, 'reviewFlag'])->name('flags.review');
    Route::post('/inbox/{conversation}/ringkasan', [Counselor\SummaryController::class, 'store'])->name('summaries.store')->middleware('throttle:summaries');
    Route::get('/ringkasan/{summary}', [Counselor\SummaryController::class, 'show'])->name('summaries.show');
    Route::post('/ringkasan/{summary}/salin', [Counselor\SummaryController::class, 'copyToNote'])->name('summaries.copy');

    Route::get('/kasus', [Counselor\CaseController::class, 'index'])->name('cases');
    Route::post('/kasus/kolom', [Counselor\CaseBoardController::class, 'store'])->name('board-columns.store');
    Route::patch('/kasus/kolom/{column}', [Counselor\CaseBoardController::class, 'update'])->name('board-columns.update');
    Route::delete('/kasus/kolom/{column}', [Counselor\CaseBoardController::class, 'destroy'])->name('board-columns.destroy');
    Route::patch('/inbox/{conversation}/papan', [Counselor\CaseBoardController::class, 'move'])->name('conversations.board');

    Route::get('/jadwal', [Counselor\ScheduleController::class, 'index'])->name('schedule');
    Route::post('/jadwal/slot', [Counselor\ScheduleController::class, 'storeSlot'])->name('slots.store');
    Route::patch('/jadwal/slot/{slot}', [Counselor\ScheduleController::class, 'toggleBlock'])->name('slots.toggle');
    Route::delete('/jadwal/slot/{slot}', [Counselor\ScheduleController::class, 'destroySlot'])->name('slots.destroy');
    Route::patch('/jadwal/sesi/{session:id}', [Counselor\ScheduleController::class, 'updateSession'])->name('sessions.update');

    Route::get('/self-help', [Counselor\ArticleController::class, 'index'])->name('selfhelp.index');
    Route::get('/self-help/baru', [Counselor\ArticleController::class, 'create'])->name('selfhelp.create');
    Route::post('/self-help', [Counselor\ArticleController::class, 'store'])->name('selfhelp.store');
    Route::post('/self-help/gambar', [Counselor\ArticleController::class, 'uploadImage'])->middleware('throttle:media')->name('selfhelp.image');
    Route::get('/self-help/{article}', [Counselor\ArticleController::class, 'show'])->name('selfhelp.show');
    Route::post('/self-help/{article}/komentar', [Counselor\ArticleController::class, 'comment'])->name('selfhelp.comments.store');
    Route::delete('/komentar-artikel/{comment}', [Counselor\ArticleController::class, 'destroyComment'])->name('selfhelp.comments.destroy');
    Route::get('/self-help/{article}/edit', [Counselor\ArticleController::class, 'edit'])->name('selfhelp.edit');
    Route::put('/self-help/{article}', [Counselor\ArticleController::class, 'update'])->name('selfhelp.update');
    Route::delete('/self-help/{article}', [Counselor\ArticleController::class, 'destroy'])->name('selfhelp.destroy');
    Route::post('/self-help/{article}/terbitkan', [Counselor\ArticleController::class, 'publish'])->name('selfhelp.publish');
    Route::post('/cerita-teman/{story}', [Counselor\StoryModerationController::class, 'moderate'])->name('stories.moderate');

    Route::get('/profil', [Counselor\ProfileController::class, 'edit'])->name('profile');
    Route::put('/profil', [Counselor\ProfileController::class, 'update'])->name('profile.update');
    Route::put('/profil/jam-layanan', [Counselor\ProfileController::class, 'updateHours'])->name('profile.hours');
    Route::put('/profil/auto-reply', [Counselor\ProfileController::class, 'updateAutoReply'])->name('profile.auto-reply');
    Route::post('/template', [Counselor\ReplyTemplateController::class, 'store'])->name('templates.store');
    Route::put('/template/{template}', [Counselor\ReplyTemplateController::class, 'update'])->name('templates.update');
    Route::delete('/template/{template}', [Counselor\ReplyTemplateController::class, 'destroy'])->name('templates.destroy');
});

// ---------- Admin Sekolah ----------
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin', 'password.changed'])->group(function () {
    Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/akun', [Admin\AccountController::class, 'index'])->name('accounts');
    Route::post('/akun', [Admin\AccountController::class, 'store'])->name('accounts.store');
    Route::post('/akun/{user}/reset-password', [Admin\AccountController::class, 'resetPassword'])->name('accounts.reset-password');
    Route::post('/akun/{user}/status', [Admin\AccountController::class, 'toggleActive'])->name('accounts.toggle');
    Route::post('/akun/{user}/lepaskan', [Admin\AccountController::class, 'releaseFromSchool'])->name('accounts.release');
    Route::post('/pindah-sekolah/{transfer}/setujui', [Admin\SchoolTransferController::class, 'approve'])->name('transfers.approve');
    Route::post('/pindah-sekolah/{transfer}/tolak', [Admin\SchoolTransferController::class, 'reject'])->name('transfers.reject');

    Route::get('/pengaturan', [Admin\SettingsController::class, 'index'])->name('settings');
    Route::put('/pengaturan/sekolah', [Admin\SettingsController::class, 'updateSchool'])->name('settings.school');
    Route::post('/laporan-iseng/{conversation}/keluarkan', [Admin\PrankReportController::class, 'ban'])->name('pranks.ban');
    Route::post('/pengaturan/kontak', [Admin\EmergencyContactController::class, 'store'])->name('contacts.store');
    Route::put('/pengaturan/kontak/{contact}', [Admin\EmergencyContactController::class, 'update'])->name('contacts.update');
    Route::delete('/pengaturan/kontak/{contact}', [Admin\EmergencyContactController::class, 'destroy'])->name('contacts.destroy');
    Route::post('/pengaturan/kontak/urutan', [Admin\EmergencyContactController::class, 'reorder'])->name('contacts.reorder');
    Route::post('/pengaturan/kata-kunci', [Admin\KeywordController::class, 'store'])->name('keywords.store');
    Route::put('/pengaturan/kata-kunci/{keyword}', [Admin\KeywordController::class, 'update'])->name('keywords.update');
    Route::delete('/pengaturan/kata-kunci/{keyword}', [Admin\KeywordController::class, 'destroy'])->name('keywords.destroy');
});
