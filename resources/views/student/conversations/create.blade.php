@extends('layouts.student', ['active' => 'cerita'])
@section('title', 'Mulai Cerita')
@section('header')
<header class="flex items-center justify-between gap-4 flex-wrap no-print">
    <div class="flex flex-col gap-1">
        <nav aria-label="Breadcrumb" class="flex gap-2 text-sm font-bold"><a href="{{ route('student.conversations.index') }}" class="text-ink-soft no-underline">Cerita</a><span class="text-[#B9AFA0]">/</span><span>Cerita baru</span></nav>
        <h1 class="text-[28px] lg:text-[34px] leading-tight">Mulai Cerita</h1>
    </div>
</header>
@endsection
@section('content')
<form method="POST" action="{{ route('student.conversations.store') }}" data-module="start-story"
      x-data="startStory({{ Js::from([
        'counselors' => $counselors,
        'topics' => $topics,
        'preselect' => $preselect,
        'old' => [
            'counselor_id' => old('counselor_id', $preselect),
            'topics' => array_map('intval', old('topics', [])),
            'is_anonymous' => old('is_anonymous', '1') === '1',
        ],
        'hasErrors' => $errors->any(),
        'studentName' => $student->name ?? '',
        'studentClass' => $student->studentProfile?->class_name,
      ]) }})"
      x-on:submit="submitting = true"
      class="bg-white rounded-[32px] shadow-card flex flex-wrap overflow-hidden lg:min-h-[680px]">
    @csrf
    <input type="hidden" name="counselor_id" :value="counselor === 'any' ? '' : counselor">
    <template x-for="t in selectedTopics" :key="t"><input type="hidden" name="topics[]" :value="t"></template>
    <input type="hidden" name="is_anonymous" :value="anonymous ? 1 : 0">
    @if($mood)<input type="hidden" name="mood_entry_id" value="{{ $mood->id }}">@endif

    {{-- Stepper (desktop) --}}
    <ol aria-label="Langkah" class="hidden lg:flex m-0 px-5 py-7 list-none flex-[1_1_240px] max-w-[290px] bg-cream-light border-r border-line-soft flex-col gap-1.5">
        <template x-for="(s, i) in steps" :key="i">
            <li>
                <button type="button" x-on:click="go(i + 1)" :aria-current="step === i + 1 ? 'step' : 'false'" class="w-full flex items-center gap-3 p-3 rounded-2xl text-ink text-left" :class="step === i + 1 ? 'bg-white shadow-[0_4px_14px_rgba(62,53,48,0.08)]' : ''" :style="i + 1 <= maxStep ? '' : 'cursor: default'">
                    <span class="w-8 h-8 flex-none rounded-full flex items-center justify-center text-sm font-extrabold" :class="step === i + 1 ? 'bg-terracotta-deep text-white' : (i + 1 < step || i + 1 < maxStep ? 'bg-sage-deep text-white' : 'bg-cream-deep text-ink-soft')"><x-icon name="check" size="16" stroke="3" x-show="i + 1 < maxStep && step !== i + 1" /><span x-show="!(i + 1 < maxStep && step !== i + 1)" x-text="i + 1"></span></span>
                    <span class="flex flex-col gap-0.5"><span class="text-[15px] font-extrabold" x-text="s.title"></span><span class="text-xs font-semibold text-ink-soft" x-text="i + 1 <= maxStep ? summary(i + 1) : 'Belum diisi'"></span></span>
                </button>
            </li>
        </template>
        <li class="mt-auto p-3.5 rounded-[18px] bg-sage-tint text-[13px] leading-normal text-sage-ink">Kamu bisa berhenti kapan saja. Tidak ada yang terkirim sebelum kamu menekan <b>Mulai Chat</b>.</li>
    </ol>

    <section class="flex-[999_1_480px] min-w-0 px-5 py-6 sm:px-[clamp(24px,3vw,44px)] sm:py-8 flex flex-col gap-[22px]">
        {{-- Progress (mobile) --}}
        <div class="lg:hidden flex gap-1.5 items-center" aria-hidden="true">
            <template x-for="i in steps.length"><span class="flex-1 h-1.5 rounded-full" :class="i <= step ? 'bg-terracotta-deep' : 'bg-line'"></span></template>
        </div>
        <div class="flex flex-col gap-2">
            <span class="text-[13px] font-extrabold text-ink-soft" x-text="'Langkah ' + step + ' dari ' + steps.length"></span>
            <h2 class="text-[26px] sm:text-[30px] leading-[1.2]" x-text="steps[step - 1].heading" tabindex="-1" x-ref="heading"></h2>
            <p class="text-base leading-relaxed text-ink-soft max-w-[640px]" x-text="steps[step - 1].sub"></p>
        </div>

        @if($mood)
            <div class="flex items-center gap-3 p-3.5 rounded-card bg-sand-tint text-sand-ink2 text-sm font-bold"><x-mood-face :mood="$mood->mood" size="26" />Catatan mood {{ $mood->entry_date->translatedFormat('j M') }} akan ikut dibagikan di cerita ini.</div>
        @endif
        @if($errors->any())
            <div class="p-4 rounded-card bg-terracotta-tint text-terracotta-ink text-sm font-bold" role="alert">{{ $errors->first() }}</div>
        @endif

        {{-- 1. Guru --}}
        <div x-show="step === 1" class="flex flex-col gap-3">
            <div role="radiogroup" aria-label="Pilih Guru BK" class="grid grid-cols-[repeat(auto-fit,minmax(min(280px,100%),1fr))] gap-3">
                <template x-for="g in counselorOptions" :key="g.id">
                    <button type="button" role="radio" :aria-checked="(counselor === g.id).toString()" x-on:click="counselor = g.id" class="flex items-center gap-3.5 p-4 rounded-[22px] bg-white text-ink text-left transition" :class="counselor === g.id ? 'border-2 border-terracotta-deep shadow-[0_6px_18px_rgba(181,82,63,0.12)]' : 'border-[1.5px] border-line'">
                        <span class="relative w-14 h-14 flex-none rounded-[18px] flex items-center justify-center font-display text-2xl overflow-hidden" :style="'background:' + g.palette[0] + '; color:' + g.palette[1]">
                            <template x-if="g.photo"><img :src="g.photo" alt="" class="w-full h-full object-cover"></template>
                            <span x-show="!g.photo" x-text="g.initial"></span>
                        </span>
                        <span class="flex-1 flex flex-col gap-0.5"><span class="font-extrabold text-base" x-text="g.name"></span><span class="text-[13px] text-ink-soft" x-text="g.note"></span></span>
                        <span class="w-[22px] h-[22px] flex-none rounded-full" :class="counselor === g.id ? 'border-[7px] border-terracotta-deep bg-white' : 'border-2 border-toggle-off'"></span>
                    </button>
                </template>
            </div>
            <a href="{{ route('student.counselors') }}" class="self-start text-sm font-extrabold text-terracotta-deep">Belum kenal? Lihat profil Guru BK</a>
        </div>

        {{-- 2. Topik --}}
        <div x-show="step === 2" x-cloak class="flex flex-col gap-4">
            <div role="group" aria-label="Topik" class="flex flex-wrap gap-2.5">
                <template x-for="t in topics" :key="t.id">
                    <button type="button" :aria-pressed="selectedTopics.includes(t.id).toString()" x-on:click="toggleTopic(t.id)" class="h-12 px-5 rounded-full text-[15px] transition" :class="selectedTopics.includes(t.id) ? 'border-[1.5px] border-terracotta-deep bg-terracotta-deep text-white font-extrabold' : (t.slug === 'belum-tahu' ? 'border-[1.5px] border-dashed border-sand-deep text-sand-ink font-bold' : 'border-[1.5px] border-line bg-white text-ink font-bold')" x-text="t.name"></button>
                </template>
            </div>
            <div class="flex gap-2.5 p-4 rounded-[18px] bg-cream-light border border-line text-sm leading-normal text-[#5A4E47] max-w-[640px]"><x-icon name="info" size="20" class="text-sand-deep" /><span>Topik hanya membantu Guru BK bersiap, bukan label untukmu.</span></div>
        </div>

        {{-- 3. Identitas --}}
        <div x-show="step === 3" x-cloak class="flex flex-wrap gap-5 items-start">
            <div class="flex-[1_1_300px] flex flex-col gap-3">
                <div role="radiogroup" aria-label="Mode identitas" class="flex flex-col gap-2.5">
                    <button type="button" role="radio" :aria-checked="anonymous.toString()" x-on:click="anonymous = true" class="flex flex-col gap-1 px-[18px] py-4 rounded-card text-ink text-left" :class="anonymous ? 'border-2 border-terracotta-deep bg-terracotta-tint2' : 'border-[1.5px] border-line bg-white'"><span class="font-extrabold text-base">Anonim <span class="text-xs font-bold text-sage-ink2">(disarankan)</span></span><span class="text-sm text-ink-soft leading-normal">Nama, kelas, dan email tersembunyi. Guru melihat nama samaran.</span></button>
                    @if($student->hasFilledIdentity())
                        <button type="button" role="radio" :aria-checked="(!anonymous).toString()" x-on:click="anonymous = false" class="flex flex-col gap-1 px-[18px] py-4 rounded-card text-ink text-left" :class="!anonymous ? 'border-2 border-terracotta-deep bg-terracotta-tint2' : 'border-[1.5px] border-line bg-white'"><span class="font-extrabold text-base">Tampilkan Nama</span><span class="text-sm text-ink-soft leading-normal">Guru melihat nama & kelas yang kamu isi di Profil.</span></button>
                    @else
                        <div class="flex flex-col gap-1 px-[18px] py-4 rounded-card text-ink-soft border-[1.5px] border-dashed border-line bg-white"><span class="font-extrabold text-base">Tampilkan Nama</span><span class="text-sm leading-normal">Kamu belum mengisi nama. <a href="{{ route('student.profile') }}#identitas" class="font-extrabold text-terracotta-deep">Isi di Profil</a> kalau ingin bercerita dengan nama.</span></div>
                    @endif
                </div>
                @error('is_anonymous')<p class="field-error" role="alert">{{ $message }}</p>@enderror
                <div class="flex gap-2.5 p-4 rounded-[18px] bg-sage-tint text-sm leading-normal text-sage-ink"><x-icon name="lock" size="20" /><span>Kamu bisa membuka identitas nanti kapan saja.</span></div>
            </div>
            <div class="flex-[1_1_300px] bg-cream rounded-card-l p-5 flex flex-col gap-3">
                <span class="text-xs font-extrabold tracking-[0.08em] uppercase text-ink-soft" x-text="'Yang dilihat ' + counselorName()"></span>
                <div class="flex items-center gap-3 p-3.5 rounded-[18px] bg-white">
                    <span class="w-12 h-12 flex-none rounded-2xl flex items-center justify-center font-display text-xl" :class="anonymous ? 'bg-sage-tint text-sage-ink2' : 'bg-terracotta-tint text-terracotta-ink'" x-text="anonymous ? '?' : studentName.charAt(0)"></span>
                    <span class="flex flex-col gap-0.5"><span class="font-extrabold text-[15px]" x-text="anonymous ? 'Nama samaran acak (mis. Kucing Oranye #482)' : studentName + ' · ' + studentClass"></span><span class="text-[13px] text-ink-soft" x-text="topicSummary()"></span></span>
                </div>
            </div>
        </div>

        <p x-show="stepError" x-text="stepError" x-cloak class="field-error" role="alert"></p>

        <div class="mt-auto pt-[18px] border-t border-line-soft flex justify-between gap-3 flex-wrap">
            <a x-show="step === 1" href="{{ route('student.home') }}" class="btn btn-outline !h-[52px]">Batal</a>
            <button x-show="step > 1" x-cloak type="button" x-on:click="prev()" class="btn btn-outline !h-[52px]">Kembali</button>
            <button x-show="step < steps.length" type="button" x-on:click="next()" class="btn btn-primary !h-[52px] !px-8">Lanjut</button>
            <button x-show="step === steps.length" x-cloak type="submit" :disabled="submitting" class="btn btn-primary !h-[52px] !px-[30px]"><x-icon name="chat" size="18" /><span x-text="submitting ? 'Membuka chat…' : 'Mulai Chat'"></span></button>
        </div>
    </section>
</form>
@endsection
