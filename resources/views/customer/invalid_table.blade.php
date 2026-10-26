@extends('layouts.app')

@section('title', 'QR Code Meja Tidak Valid | Kafe Kayla')

@section('content')
<div class="min-h-screen flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-sm w-full text-center border border-stone-200 shadow-xl space-y-5">
        <div class="w-16 h-16 rounded-3xl bg-amber-100 text-amber-800 flex items-center justify-center text-3xl mx-auto shadow-inner">
            📷
        </div>

        <div class="space-y-2">
            <h1 class="font-extrabold text-xl text-stone-900">QR Code Tidak Valid</h1>
            <p class="text-xs text-stone-500 leading-relaxed">
                Akses ke Meja <strong>{{ $tableNumber }}</strong> memerlukan QR code meja resmi. Silakan pindai ulang QR code fisik yang tertera di atas meja kafe Anda.
            </p>
        </div>

        <div class="pt-2 border-t border-stone-100">
            <a href="{{ route('home') }}" class="block w-full bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition">
                Kembali ke Halaman Utama
            </a>
        </div>
    </div>
</div>
@endsection
