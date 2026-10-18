@extends('errors.layout')
@section('title', 'Sesi berakhir')
@section('code', 'Kode 419')
@section('heading', 'Sesimu sudah berakhir')
@section('message', 'Demi keamananmu, halaman ini perlu dimuat ulang. Silakan coba lagi ya.')
@section('action')<a href="{{ url()->previous() }}" class="btn btn-primary">Muat ulang</a>@endsection
