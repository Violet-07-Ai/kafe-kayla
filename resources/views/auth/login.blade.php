@extends('layouts.app')

@section('title', 'Masuk Staf - Kafe Kayla')

@section('content')
<div x-data="loginForm()" class="min-h-screen flex items-center justify-center p-4 bg-stone-100">
    <div class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-stone-200 shadow-2xl space-y-6">

        <!-- Logo & Header -->
        <div class="text-center space-y-2">
            <div class="w-14 h-14 rounded-2xl bg-amber-600 flex items-center justify-center font-black text-white text-3xl mx-auto shadow-lg shadow-amber-600/30">
                K
            </div>
            <h1 class="text-2xl font-black text-stone-900 tracking-tight">Portal Staf Kafe Kayla</h1>
            <p class="text-xs text-stone-500 font-medium">Masuk untuk mengelola pesanan, kasir, dapur & laporan</p>
        </div>

        <!-- 1-Click Demo Quick Fill Buttons -->
        <div class="bg-stone-50 border border-stone-200 rounded-2xl p-3.5 space-y-2">
            <div class="flex items-center justify-between">
                <span class="text-[11px] font-extrabold uppercase tracking-wider text-stone-500">Akses Cepat Demo:</span>
                <span class="text-[10px] text-amber-700 bg-amber-100 px-2 py-0.5 rounded-full font-bold">1-Klik Isi</span>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="fill('admin', 'admin123')" 
                        class="bg-purple-50 hover:bg-purple-100 text-purple-900 border border-purple-200 py-1.5 px-2 rounded-xl text-xs font-bold transition">
                    👑 Admin
                </button>
                <button type="button" @click="fill('kasir', 'kasir123')" 
                        class="bg-emerald-50 hover:bg-emerald-100 text-emerald-900 border border-emerald-200 py-1.5 px-2 rounded-xl text-xs font-bold transition">
                    💵 Kasir
                </button>
                <button type="button" @click="fill('dapur', 'dapur123')" 
                        class="bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 py-1.5 px-2 rounded-xl text-xs font-bold transition">
                    🍳 Dapur
                </button>
            </div>
        </div>

        <!-- Login Form -->
        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1">
                <label for="email" class="block text-xs font-extrabold uppercase tracking-wider text-stone-700">Email atau Username</label>
                <input type="text" id="email" name="email" x-model="email" required autofocus
                       placeholder="admin / kasir / dapur"
                       class="w-full px-4 py-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-sm transition">
                @error('email')
                    <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <div class="space-y-1">
                <label for="password" class="block text-xs font-extrabold uppercase tracking-wider text-stone-700">Kata Sandi</label>
                <input type="password" id="password" name="password" x-model="password" required
                       placeholder="••••••••"
                       class="w-full px-4 py-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none text-sm transition">
                @error('password')
                    <span class="text-xs text-rose-600 font-semibold">{{ $message }}</span>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center space-x-2 cursor-pointer text-stone-600">
                    <input type="checkbox" name="remember" class="rounded text-amber-600 focus:ring-amber-500">
                    <span>Ingat Saya</span>
                </label>
            </div>

            <button type="submit" 
                    class="w-full bg-stone-900 hover:bg-stone-800 text-white font-extrabold text-sm py-3.5 px-4 rounded-xl shadow-lg transition transform active:scale-98">
                Masuk ke Sistem
            </button>
        </form>

        <div class="pt-3 border-t border-stone-100 text-center">
            <a href="{{ route('home') }}" class="text-xs font-bold text-amber-700 hover:underline">
                &larr; Kembali ke Beranda / Menu Meja
            </a>
        </div>
    </div>
</div>

<script>
function loginForm() {
    return {
        email: '{{ old('email') }}',
        password: '',
        fill(username, pwd) {
            this.email = username;
            this.password = pwd;
        }
    }
}
</script>
@endsection
