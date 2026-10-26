@extends('layouts.staff')

@section('title', 'Manajemen Akun Staf - Kafe Kayla')

@section('content')
<div x-data="{ createStaffModal: false }" class="space-y-6">

    <!-- Header & Action -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-stone-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight">Akun Staf & Hak Akses</h1>
            <p class="text-xs text-stone-500 font-medium">Kelola akun dan peranan untuk Administrator, Kasir, dan Staf Dapur</p>
        </div>

        <button @click="createStaffModal = true" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition flex items-center space-x-1.5 self-start sm:self-auto">
            <span>+ Buat Akun Staf</span>
        </button>
    </div>

    <!-- Staff List Table -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-bold uppercase tracking-wider text-[10px] border-b border-stone-200">
                    <tr>
                        <th class="py-3.5 px-4">Nama Lengkap</th>
                        <th class="py-3.5 px-4">Username</th>
                        <th class="py-3.5 px-4">Email</th>
                        <th class="py-3.5 px-4">Peran (Role)</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($users as $user)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="py-3.5 px-4 font-bold text-stone-900">{{ $user->name }}</td>
                            <td class="py-3.5 px-4 font-mono text-stone-600">{{ $user->username }}</td>
                            <td class="py-3.5 px-4 text-stone-500">{{ $user->email }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold uppercase
                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-900 border border-purple-300' : '' }}
                                    {{ $user->role === 'cashier' ? 'bg-emerald-100 text-emerald-900 border border-emerald-300' : '' }}
                                    {{ $user->role === 'kitchen' ? 'bg-amber-100 text-amber-900 border border-amber-300' : '' }}">
                                    {{ $user->role }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $user->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                @if($user->id !== auth()->id())
                                    <form action="{{ route('admin.staff.toggle', $user) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-xs font-bold {{ $user->is_active ? 'text-rose-600 hover:text-rose-800' : 'text-emerald-600 hover:text-emerald-800' }}">
                                            {{ $user->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                @else
                                    <span class="text-stone-400 text-[11px] italic">Akun Anda</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- CREATE STAFF MODAL -->
    <div x-cloak x-show="createStaffModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="createStaffModal = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-3xl p-6 max-w-sm w-full z-10 shadow-2xl space-y-4">
            <h3 class="font-extrabold text-base text-stone-900">Buat Akun Staf Baru</h3>
            <form action="{{ route('admin.staff.store') }}" method="POST" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Nama Lengkap</label>
                    <input type="text" name="name" required placeholder="Nama staf" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Username</label>
                    <input type="text" name="username" required placeholder="username_staf" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Email</label>
                    <input type="email" name="email" required placeholder="staf@kafekayla.com" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Peran (Role)</label>
                    <select name="role" required class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="cashier">Kasir (Cashier)</option>
                        <option value="kitchen">Staf Dapur (Kitchen)</option>
                        <option value="admin">Administrator (Admin)</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Kata Sandi</label>
                    <input type="password" name="password" required minlength="6" placeholder="Minimal 6 karakter" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="flex space-x-2 pt-2">
                    <button type="button" @click="createStaffModal = false" class="w-1/2 bg-stone-100 font-bold text-xs py-3 rounded-xl text-stone-700">Batal</button>
                    <button type="submit" class="w-1/2 bg-amber-600 hover:bg-amber-700 font-bold text-xs py-3 rounded-xl text-white shadow-md">Simpan Akun</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
