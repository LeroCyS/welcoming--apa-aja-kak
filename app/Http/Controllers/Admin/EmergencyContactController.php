<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmergencyContact;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmergencyContactController extends Controller
{
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:40'],
            'whatsapp' => ['nullable', 'string', 'max:40'],
            'is_active' => ['boolean'],
        ];
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate($this->rules(), [], ['name' => 'nama kontak']);
        $school = School::current();
        $data['school_id'] = $school->id;
        $data['sort_order'] = (int) $school->emergencyContacts()->max('sort_order') + 1;
        $data['is_active'] = $request->boolean('is_active', true);
        EmergencyContact::create($data);

        return redirect()->route('admin.settings', ['tab' => 'kontak'])->with('status', 'Kontak darurat ditambahkan.');
    }

    public function update(Request $request, EmergencyContact $contact): RedirectResponse
    {
        $this->ensureSameSchool($contact);
        $data = $request->validate($this->rules(), [], ['name' => 'nama kontak']);
        $data['is_active'] = $request->boolean('is_active');
        $contact->update($data);

        return redirect()->route('admin.settings', ['tab' => 'kontak'])->with('status', 'Kontak darurat diperbarui.');
    }

    public function destroy(EmergencyContact $contact): RedirectResponse
    {
        $this->ensureSameSchool($contact);
        $contact->delete();

        return redirect()->route('admin.settings', ['tab' => 'kontak'])->with('status', 'Kontak darurat dihapus.');
    }

    public function reorder(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);
        foreach ($data['ids'] as $i => $id) {
            EmergencyContact::where('school_id', $request->user()->school_id)->where('id', $id)->update(['sort_order' => $i + 1]);
        }

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }
}
