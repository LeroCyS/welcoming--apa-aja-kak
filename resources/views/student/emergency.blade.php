@extends('layouts.student', ['active' => 'darurat'])
@section('title', 'Bantuan Darurat')
@section('back', $back)
@section('heading', 'Bantuan Darurat')
@section('content')
<div class="flex flex-col gap-9">
    @include('public.partials.emergency-content')
</div>
@endsection
