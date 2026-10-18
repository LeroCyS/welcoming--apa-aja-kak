<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\FollowUp;
use App\Services\CaseStatusService;
use App\Services\ConversationMessenger;
use App\Support\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function respond(Request $request, FollowUp $followUp, ConversationMessenger $messenger, CaseStatusService $status): RedirectResponse
    {
        $c = $followUp->conversation;
        $this->authorize('reply', $c);
        abort_if($followUp->response !== null || ! $followUp->sent_at, 409, 'Follow-up ini sudah dijawab.');
        $data = $request->validate(['response' => ['required', Rule::in(['better', 'same', 'worse'])]]);

        $followUp->update(['response' => $data['response'], 'responded_at' => now()]);

        switch ($data['response']) {
            case 'better':
                $messenger->system($c, 'Senang dengarnya! Mau berbagi cerita tanpa nama untuk menguatkan teman sekolahmu? Kamu bisa menulisnya di menu Cerita Teman.', 'system', ['testimonial_cta' => true]);
                break;
            case 'same':
                $status->change($c, 'monitoring', null, 'Follow-up: murid merasa sama saja');
                $messenger->system($c, 'Makasih sudah jujur. Kamu bisa lanjut cerita di sini kapan saja.');
                Notify::counselor($c, 'follow_up', 'Follow-up: '.$c->displayNameFor($c->counselor).' merasa “sama aja”');
                break;
            case 'worse':
                $status->change($c, 'monitoring', null, 'Follow-up: murid merasa makin berat');
                $messenger->refreshPriority($c);
                $messenger->sendEmergencyInfo($c, true);
                Notify::counselor($c, 'follow_up_worse', 'Prioritas: '.$c->displayNameFor($c->counselor).' merasa makin berat', true);
                break;
        }

        return redirect()->route('student.conversations.show', $c)->with('status', 'Terima kasih sudah memberi kabar.');
    }
}
