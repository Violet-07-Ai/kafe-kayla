@extends('layouts.staff')

@section('title', 'Manajemen Meja & QR Code - Kafe Kayla')

@section('content')
<div x-data="{ createTableModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-stone-200 shadow-sm print:hidden">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight">Manajemen Meja & QR Code</h1>
            <p class="text-xs text-stone-500 font-medium">Generate dan cetak QR Code unik untuk setiap meja pelanggan</p>
        </div>

        <div class="flex items-center space-x-2">
            <button onclick="window.print()" class="bg-stone-900 hover:bg-stone-800 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition flex items-center space-x-1.5">
                <span>🖨️ Cetak Semua QR</span>
            </button>
            <button @click="createTableModal = true" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition flex items-center space-x-1.5">
                <span>+ Tambah Meja</span>
            </button>
        </div>
    </div>

    <!-- Grid of Tables & QR Codes -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($tables as $table)
            <div class="bg-white rounded-3xl p-5 border border-stone-200 shadow-sm flex flex-col justify-between text-center space-y-4 break-inside-avoid">
                <div class="space-y-1">
                    <span class="inline-block text-base font-black px-4 py-1 rounded-full bg-stone-900 text-white">
                        Meja {{ $table->table_number }}
                    </span>
                    <p class="text-xs text-stone-400">Kapasitas: {{ $table->capacity }} Orang</p>
                </div>

                <!-- QR Code SVG Image -->
                <div class="p-3 bg-stone-50 rounded-2xl border border-stone-200 inline-block mx-auto shadow-inner">
                    @if(!empty($table->qr_data_uri))
                        <img src="{{ $table->qr_data_uri }}" alt="QR Meja {{ $table->table_number }}" class="w-40 h-40 mx-auto">
                    @endif
                </div>

                <div class="text-xs text-stone-500 font-mono break-all line-clamp-1">
                    {{ $table->qr_url }}
                </div>

                <!-- Admin Action Buttons (Hidden when printing) -->
                <div class="pt-2 border-t border-stone-100 flex items-center justify-between gap-2 print:hidden">
                    <a href="{{ $table->qr_url }}" target="_blank" class="text-xs font-bold text-amber-700 hover:underline">
                        Buka Menu &rarr;
                    </a>

                    <div class="flex items-center space-x-1">
                        <!-- Regenerate Token -->
                        <form action="{{ route('admin.tables.regenerate_qr', $table) }}" method="POST" onsubmit="return confirm('Generate QR baru? QR lama tidak akan berlaku lagi.')">
                            @csrf
                            <button type="submit" title="Generate Token QR Baru" class="p-2 rounded-xl text-stone-500 hover:bg-stone-100 transition text-xs font-bold">
                                🔄 Token Baru
                            </button>
                        </form>

                        <!-- Toggle Active -->
                        <form action="{{ route('admin.tables.toggle', $table) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold {{ $table->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800' }}">
                                {{ $table->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full bg-white rounded-3xl p-12 text-center border border-stone-200">
                <p class="text-stone-400 font-bold text-sm">Belum ada meja yang didaftarkan.</p>
            </div>
        @endforelse
    </div>

    <!-- CREATE TABLE MODAL -->
    <div x-cloak x-show="createTableModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 print:hidden">
        <div @click="createTableModal = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full z-10 shadow-2xl space-y-4">
            <h3 class="font-extrabold text-base text-stone-900">Tambah Meja Kafe</h3>
            <form action="{{ route('admin.tables.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Nomor Meja</label>
                    <input type="text" name="table_number" required placeholder="Contoh: 11 atau VIP-1" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Kapasitas Kursi</label>
                    <input type="number" name="capacity" value="4" min="1" max="50" required class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="flex space-x-2 pt-2">
                    <button type="button" @click="createTableModal = false" class="w-1/2 bg-stone-100 font-bold text-xs py-3 rounded-xl text-stone-700">Batal</button>
                    <button type="submit" class="w-1/2 bg-amber-600 hover:bg-amber-700 font-bold text-xs py-3 rounded-xl text-white shadow-md">Simpan</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
