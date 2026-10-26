@extends('layouts.staff')

@section('title', 'Manajemen Menu & Variasi - Kafe Kayla')

@section('content')
<div x-data="adminMenuApp()" class="space-y-6">

    <!-- Top Header & Add Button -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-5 rounded-3xl border border-stone-200 shadow-sm">
        <div>
            <h1 class="text-xl font-black text-stone-900 tracking-tight">Katalog Menu & Variasi</h1>
            <p class="text-xs text-stone-500 font-medium">Kelola daftar produk makanan & minuman, harga, dan opsi variasi</p>
        </div>

        <button @click="createMenuModal = true" class="bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs py-3 px-4 rounded-xl shadow-md transition flex items-center space-x-1.5 self-start sm:self-auto">
            <span>+ Tambah Menu Baru</span>
        </button>
    </div>

    <!-- Menu Table Card -->
    <div class="bg-white rounded-3xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-stone-50 text-stone-500 font-bold uppercase tracking-wider text-[10px] border-b border-stone-200">
                    <tr>
                        <th class="py-3.5 px-4">Menu</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Harga Dasar</th>
                        <th class="py-3.5 px-4">Variasi Tersedia</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($menus as $menu)
                        <tr class="hover:bg-stone-50 transition">
                            <td class="py-3.5 px-4">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold text-lg shrink-0 overflow-hidden">
                                        @if($menu->image)
                                            <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                                        @else
                                            <span>☕</span>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-bold text-stone-900 text-sm">{{ $menu->name }}</div>
                                        <div class="text-[11px] text-stone-400 line-clamp-1">{{ $menu->description }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="bg-stone-100 text-stone-700 font-bold px-2.5 py-1 rounded-lg text-[11px]">
                                    {{ $menu->category->name }}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 font-black text-stone-900 text-sm">
                                Rp {{ number_format($menu->base_price, 0, ',', '.') }}
                            </td>

                            <!-- Variation summary & manage button -->
                            <td class="py-3.5 px-4">
                                <div class="space-y-1">
                                    @forelse($menu->variationGroups as $group)
                                        <div class="text-[11px] text-stone-600">
                                            <strong>{{ $group->name }}:</strong>
                                            {{ $group->options->map(fn($o) => $o->name . ($o->extra_price > 0 ? '(+' . number_format($o->extra_price, 0) . ')' : ''))->implode(', ') }}
                                        </div>
                                    @empty
                                        <span class="text-stone-400 italic text-[11px]">Tanpa variasi</span>
                                    @endforelse
                                    <button @click="openVariationModal({{ json_encode($menu) }})" class="text-[10px] text-amber-700 hover:underline font-bold block pt-0.5">
                                        + Atur Variasi
                                    </button>
                                </div>
                            </td>

                            <!-- Toggle Status Available/Habis -->
                            <td class="py-3.5 px-4">
                                <form action="{{ route('admin.menus.toggle', $menu) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-extrabold transition
                                        {{ $menu->is_available ? 'bg-emerald-100 text-emerald-800 hover:bg-emerald-200' : 'bg-rose-100 text-rose-800 hover:bg-rose-200' }}">
                                        {{ $menu->is_available ? '✓ Tersedia' : '✗ Habis' }}
                                    </button>
                                </form>
                            </td>

                            <!-- Actions -->
                            <td class="py-3.5 px-4 text-right space-x-2">
                                <form action="{{ route('admin.menus.destroy', $menu) }}" method="POST" class="inline" onsubmit="return confirm('Hapus menu ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-xs">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-stone-400">Belum ada menu yang didaftarkan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-stone-200">
            {{ $menus->links() }}
        </div>
    </div>

    <!-- CREATE MENU MODAL -->
    <div x-cloak x-show="createMenuModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="createMenuModal = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-3xl p-6 max-w-md w-full z-10 shadow-2xl space-y-4">
            <h3 class="font-extrabold text-base text-stone-900">Tambah Menu Baru</h3>
            <form action="{{ route('admin.menus.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Kategori</label>
                    <select name="category_id" required class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Nama Menu</label>
                    <input type="text" name="name" required placeholder="Contoh: Kopi Susu Aren" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Harga Dasar (Rp)</label>
                    <input type="number" name="base_price" required min="0" placeholder="18000" class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none">
                </div>

                <div class="space-y-1">
                    <label class="block text-xs font-bold text-stone-600">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat tentang menu..." class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>

                <div class="flex items-center space-x-2 pt-1">
                    <input type="checkbox" name="is_available" value="1" checked id="avail" class="rounded text-amber-600">
                    <label for="avail" class="text-xs font-bold text-stone-700">Langsung tersedia untuk dipesan</label>
                </div>

                <div class="flex space-x-2 pt-2">
                    <button type="button" @click="createMenuModal = false" class="w-1/2 bg-stone-100 font-bold text-xs py-3 rounded-xl text-stone-700">Batal</button>
                    <button type="submit" class="w-1/2 bg-amber-600 hover:bg-amber-700 font-bold text-xs py-3 rounded-xl text-white shadow-md">Simpan Menu</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MANAGE VARIATIONS MODAL -->
    <div x-cloak x-show="variationModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div @click="variationModalOpen = false" class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>
        <div class="bg-white rounded-3xl p-6 max-w-lg w-full z-10 shadow-2xl space-y-4 max-h-[85vh] overflow-y-auto">
            <div class="flex items-center justify-between pb-2 border-b border-stone-100">
                <div>
                    <h3 class="font-extrabold text-base text-stone-900">Atur Variasi Menu</h3>
                    <p class="text-xs text-stone-500" x-text="activeMenu?.name"></p>
                </div>
                <button @click="variationModalOpen = false" class="text-stone-400 hover:text-stone-700 font-bold text-xl">&times;</button>
            </div>

            <!-- Add Variation Group Form -->
            <div class="bg-stone-50 p-3.5 rounded-2xl border border-stone-200 space-y-2">
                <h4 class="font-bold text-xs uppercase tracking-wider text-stone-700">+ Tambah Grup Variasi (misal: Ukuran, Gula)</h4>
                <form :action="'/admin/menus/' + activeMenu?.id + '/variation-group'" method="POST" class="flex gap-2">
                    @csrf
                    <input type="text" name="name" required placeholder="Nama grup (misal: Level Es)" class="flex-1 text-xs p-2.5 rounded-xl border border-stone-200 outline-none">
                    <button type="submit" class="bg-stone-900 text-white px-3 py-2 rounded-xl text-xs font-bold hover:bg-stone-800 shrink-0">
                        Tambah Grup
                    </button>
                </form>
            </div>

            <!-- Existing Variation Groups & Add Option Form -->
            <div class="space-y-3">
                <template x-for="group in activeMenu?.variation_groups" :key="group.id">
                    <div class="border border-stone-200 rounded-2xl p-3.5 space-y-2">
                        <div class="flex items-center justify-between">
                            <span class="font-black text-xs uppercase text-amber-800" x-text="group.name"></span>
                        </div>

                        <!-- Options List -->
                        <div class="space-y-1">
                            <template x-for="option in group.options" :key="option.id">
                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-stone-50">
                                    <span class="font-medium text-stone-800" x-text="option.name"></span>
                                    <div class="flex items-center space-x-2">
                                        <span class="font-bold text-stone-600" x-text="parseFloat(option.extra_price) > 0 ? '+Rp ' + formatPrice(option.extra_price) : 'Gratis'"></span>
                                        <form :action="'/admin/variation-option/' + option.id" method="POST" class="inline" onsubmit="return confirm('Hapus opsi ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-600 hover:text-rose-800 font-bold text-[11px]">&times;</button>
                                        </form>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Add Option Form -->
                        <form :action="'/admin/variation-group/' + group.id + '/option'" method="POST" class="flex gap-2 pt-1 border-t border-stone-100">
                            @csrf
                            <input type="text" name="name" required placeholder="Opsi (misal: Large)" class="flex-1 text-xs p-2 rounded-lg border border-stone-200 outline-none">
                            <input type="number" name="extra_price" value="0" min="0" placeholder="Biaya extra" class="w-24 text-xs p-2 rounded-lg border border-stone-200 outline-none">
                            <button type="submit" class="bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-amber-700 shrink-0">
                                + Opsi
                            </button>
                        </form>
                    </div>
                </template>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function adminMenuApp() {
    return {
        createMenuModal: false,
        variationModalOpen: false,
        activeMenu: null,

        openVariationModal(menu) {
            this.activeMenu = menu;
            this.variationModalOpen = true;
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID').format(val || 0);
        }
    }
}
</script>
@endpush
