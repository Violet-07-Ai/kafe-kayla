@extends('layouts.app')

@section('title', 'Menu Digital - Meja ' . $table->table_number . ' | Kafe Kayla')

@section('content')
<div x-data="customerApp()" class="min-h-screen pb-32">

    <!-- Top Sticky Header -->
    <header class="bg-white/95 backdrop-blur-md sticky top-0 z-30 border-b border-stone-200 px-4 py-3 shadow-sm">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-2xl bg-amber-600 flex items-center justify-center font-black text-white text-xl shadow-md">
                    K
                </div>
                <div>
                    <h1 class="font-extrabold text-base text-stone-900 leading-tight">Kafe Kayla</h1>
                    <p class="text-xs text-stone-500 font-medium">Pesan Mandiri via QR</p>
                </div>
            </div>

            <!-- Table Number Pill -->
            <div class="flex items-center space-x-2 bg-amber-50 border border-amber-200 px-3 py-1.5 rounded-full shadow-sm">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse"></span>
                <span class="text-xs font-black text-amber-900 tracking-wide uppercase">Meja {{ $table->table_number }}</span>
            </div>
        </div>
    </header>

    <!-- Category Tabs (Horizontal Scroll) -->
    <nav class="sticky top-[61px] z-20 bg-stone-50/95 backdrop-blur-md border-b border-stone-200/80 px-4 py-2.5">
        <div class="max-w-md mx-auto flex space-x-2 overflow-x-auto scrollbar-none py-1">
            <button @click="activeCategory = 'all'"
                    :class="activeCategory === 'all' ? 'bg-stone-900 text-white shadow-md' : 'bg-white text-stone-600 hover:bg-stone-100 border border-stone-200'"
                    class="shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all">
                Semua
            </button>
            @foreach($categories as $category)
                <button @click="activeCategory = '{{ $category->slug }}'"
                        :class="activeCategory === '{{ $category->slug }}' ? 'bg-amber-600 text-white shadow-md' : 'bg-white text-stone-600 hover:bg-stone-100 border border-stone-200'"
                        class="shrink-0 px-4 py-1.5 rounded-full text-xs font-bold transition-all">
                    {{ $category->name }}
                </button>
            @endforeach
        </div>
    </nav>

    <!-- Main Menu List -->
    <main class="max-w-md mx-auto px-4 py-4 space-y-6">
        @foreach($categories as $category)
            <section x-show="activeCategory === 'all' || activeCategory === '{{ $category->slug }}'" class="space-y-3">
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-stone-400 pl-1">
                    {{ $category->name }}
                </h2>

                <div class="space-y-3">
                    @forelse($category->menus as $menu)
                        <div @click="openModal({{ json_encode($menu) }})"
                             class="bg-white rounded-2xl p-3.5 border border-stone-200 shadow-sm hover:border-amber-400 hover:shadow-md transition cursor-pointer flex gap-3.5 items-center relative overflow-hidden group">

                            <!-- Visual Placeholder or Image -->
                            <div class="w-20 h-20 rounded-xl bg-amber-100/70 text-amber-800 flex items-center justify-center font-bold text-2xl shrink-0 overflow-hidden relative">
                                @if($menu->image)
                                    <img src="{{ asset('storage/' . $menu->image) }}" alt="{{ $menu->name }}" class="w-full h-full object-cover">
                                @else
                                    <span>☕</span>
                                @endif
                                @if(!$menu->is_available)
                                    <div class="absolute inset-0 bg-stone-900/75 flex items-center justify-center text-white text-[10px] font-black uppercase text-center p-1">
                                        Habis
                                    </div>
                                @endif
                            </div>

                            <!-- Menu Details -->
                            <div class="flex-1 min-w-0">
                                <h3 class="font-bold text-sm text-stone-900 truncate group-hover:text-amber-800 transition">
                                    {{ $menu->name }}
                                </h3>
                                <p class="text-xs text-stone-500 line-clamp-2 mt-0.5 leading-relaxed">
                                    {{ $menu->description }}
                                </p>
                                <div class="flex items-center justify-between mt-2">
                                    <span class="font-extrabold text-stone-900 text-sm">
                                        Rp {{ number_format($menu->base_price, 0, ',', '.') }}
                                    </span>
                                    <button type="button" class="w-7 h-7 rounded-full bg-amber-50 text-amber-700 flex items-center justify-center font-bold text-base hover:bg-amber-600 hover:text-white transition">
                                        +
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-stone-400 italic pl-1">Belum ada menu di kategori ini.</p>
                    @endforelse
                </div>
            </section>
        @endforeach
    </main>

    <!-- ITEM DETAIL & VARIATION MODAL -->
    <div x-cloak x-show="modalOpen" class="fixed inset-0 z-50 flex items-end sm:items-center justify-center">
        <!-- Backdrop -->
        <div @click="modalOpen = false" x-show="modalOpen" 
             x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>

        <!-- Modal Dialog -->
        <div x-show="modalOpen"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full sm:scale-95 sm:opacity-0" x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100"
             class="bg-white w-full max-w-md rounded-t-3xl sm:rounded-3xl p-5 shadow-2xl z-10 max-h-[85vh] flex flex-col relative">

            <!-- Modal Header -->
            <div class="flex items-start justify-between pb-3 border-b border-stone-100">
                <div>
                    <h3 class="font-extrabold text-lg text-stone-900" x-text="selectedMenu?.name"></h3>
                    <p class="text-xs text-stone-500 mt-0.5" x-text="selectedMenu?.description"></p>
                </div>
                <button @click="modalOpen = false" class="text-stone-400 hover:text-stone-700 text-2xl font-bold leading-none p-1">&times;</button>
            </div>

            <!-- Modal Body (Variations & Notes) -->
            <div class="flex-1 overflow-y-auto py-4 space-y-5 scrollbar-thin">
                <template x-for="group in selectedMenu?.variation_groups" :key="group.id">
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <h4 class="font-bold text-xs uppercase tracking-wider text-stone-600" x-text="group.name"></h4>
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                                  :class="group.is_required ? 'bg-amber-100 text-amber-800' : 'bg-stone-100 text-stone-600'"
                                  x-text="group.is_required ? 'Wajib' : 'Opsional'"></span>
                        </div>

                        <div class="grid grid-cols-1 gap-2">
                            <template x-for="option in group.options" :key="option.id">
                                <label class="flex items-center justify-between p-3 rounded-xl border transition cursor-pointer"
                                       :class="isOptionSelected(group, option.id) ? 'border-amber-600 bg-amber-50/50' : 'border-stone-200 hover:border-stone-300'">
                                    <div class="flex items-center space-x-3">
                                        <input :type="group.allow_multiple ? 'checkbox' : 'radio'"
                                               :name="'group_' + group.id"
                                               :checked="isOptionSelected(group, option.id)"
                                               @change="toggleOption(group, option)"
                                               class="text-amber-600 focus:ring-amber-500 w-4 h-4">
                                        <span class="text-sm font-semibold text-stone-800" x-text="option.name"></span>
                                    </div>
                                    <span class="text-xs font-bold text-stone-600"
                                          x-text="parseFloat(option.extra_price) > 0 ? '+Rp ' + formatPrice(option.extra_price) : 'Gratis'"></span>
                                </label>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Special Notes Field -->
                <div class="space-y-1.5">
                    <label class="block font-bold text-xs uppercase tracking-wider text-stone-600">Catatan Khusus (Opsional)</label>
                    <textarea x-model="itemNotes" rows="2" placeholder="Contoh: jangan terlalu manis, pisahkan saus..." 
                              class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none"></textarea>
                </div>
            </div>

            <!-- Modal Footer (Quantity & Add to Cart) -->
            <div class="pt-3 border-t border-stone-100 flex items-center space-x-3">
                <div class="flex items-center space-x-2 border border-stone-200 rounded-xl p-1 bg-stone-50">
                    <button @click="if(itemQty > 1) itemQty--" class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center font-bold text-stone-700 hover:bg-stone-200">&minus;</button>
                    <span class="w-6 text-center font-bold text-sm" x-text="itemQty"></span>
                    <button @click="itemQty++" class="w-8 h-8 rounded-lg bg-white shadow-sm flex items-center justify-center font-bold text-stone-700 hover:bg-stone-200">&plus;</button>
                </div>

                <button @click="addToCart()" 
                        class="flex-1 bg-amber-600 hover:bg-amber-700 active:scale-98 text-white py-3 px-4 rounded-xl font-bold text-sm shadow-lg shadow-amber-600/30 transition flex items-center justify-between">
                    <span>Tambah</span>
                    <span x-text="'Rp ' + formatPrice(calculateCurrentItemTotal())"></span>
                </button>
            </div>
        </div>
    </div>

    <!-- FLOATING CART BAR -->
    <div x-cloak x-show="cart.length > 0" 
         class="fixed bottom-4 left-4 right-4 z-30 max-w-md mx-auto">
        <button @click="cartDrawerOpen = true"
                class="w-full bg-stone-900 hover:bg-stone-800 text-white p-4 rounded-2xl shadow-2xl flex items-center justify-between transition transform active:scale-98 border border-stone-700">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-amber-500 text-stone-900 flex items-center justify-center font-extrabold text-sm"
                     x-text="totalCartItemsCount()"></div>
                <span class="font-bold text-sm">Lihat Keranjang</span>
            </div>
            <div class="flex items-center space-x-2">
                <span class="font-black text-base text-amber-400" x-text="'Rp ' + formatPrice(totalCartPrice())"></span>
                <svg class="w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </div>
        </button>
    </div>

    <!-- CHECKOUT DRAWER -->
    <div x-cloak x-show="cartDrawerOpen" class="fixed inset-0 z-50 flex items-end justify-center">
        <!-- Backdrop -->
        <div @click="cartDrawerOpen = false" x-show="cartDrawerOpen"
             class="fixed inset-0 bg-stone-900/60 backdrop-blur-sm"></div>

        <!-- Drawer Content -->
        <div x-show="cartDrawerOpen"
             x-transition:enter="transition ease-out duration-300" x-transition:enter-start="translate-y-full" x-transition:enter-end="translate-y-0"
             class="bg-white w-full max-w-md rounded-t-3xl p-5 shadow-2xl z-10 max-h-[90vh] flex flex-col relative">

            <!-- Drawer Header -->
            <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                <div class="flex items-center space-x-2">
                    <h3 class="font-extrabold text-lg text-stone-900">Keranjang Pesanan</h3>
                    <span class="text-xs bg-amber-100 text-amber-900 px-2 py-0.5 rounded-full font-bold">Meja {{ $table->table_number }}</span>
                </div>
                <button @click="cartDrawerOpen = false" class="text-stone-400 hover:text-stone-700 text-2xl font-bold leading-none p-1">&times;</button>
            </div>

            <!-- Drawer Body -->
            <div class="flex-1 overflow-y-auto py-4 space-y-4 scrollbar-thin">
                <template x-for="(item, idx) in cart" :key="idx">
                    <div class="bg-stone-50 rounded-2xl p-3 border border-stone-200/70 space-y-2">
                        <div class="flex items-start justify-between">
                            <div>
                                <h4 class="font-bold text-sm text-stone-900" x-text="item.name"></h4>
                                <template x-if="item.selected_options_labels && item.selected_options_labels.length > 0">
                                    <p class="text-xs text-stone-500 mt-0.5" x-text="item.selected_options_labels.join(', ')"></p>
                                </template>
                                <template x-if="item.special_notes">
                                    <p class="text-[11px] text-amber-800 bg-amber-50 px-2 py-0.5 rounded-md mt-1 inline-block" x-text="'Catatan: ' + item.special_notes"></p>
                                </template>
                            </div>
                            <span class="font-black text-sm text-stone-900" x-text="'Rp ' + formatPrice(item.subtotal)"></span>
                        </div>

                        <div class="flex items-center justify-between pt-1">
                            <button @click="removeCartItem(idx)" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">
                                Hapus
                            </button>
                            <div class="flex items-center space-x-2 border border-stone-200 rounded-lg p-0.5 bg-white">
                                <button @click="decrementCartItem(idx)" class="w-6 h-6 flex items-center justify-center font-bold text-xs text-stone-600 hover:bg-stone-100 rounded">&minus;</button>
                                <span class="w-5 text-center font-bold text-xs" x-text="item.quantity"></span>
                                <button @click="incrementCartItem(idx)" class="w-6 h-6 flex items-center justify-center font-bold text-xs text-stone-600 hover:bg-stone-100 rounded">&plus;</button>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Customer Name Field -->
                <div class="space-y-1.5 pt-2">
                    <label class="block font-bold text-xs uppercase tracking-wider text-stone-600">Nama Pelanggan (Opsional)</label>
                    <input type="text" x-model="customerName" placeholder="Nama Anda (misal: Kak Kayla)"
                           class="w-full text-xs p-3 rounded-xl border border-stone-200 focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none">
                </div>

                <!-- Payment Method Selector -->
                <div class="space-y-2 pt-2">
                    <label class="block font-bold text-xs uppercase tracking-wider text-stone-600">Metode Pembayaran</label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="flex flex-col p-3 rounded-2xl border transition cursor-pointer"
                               :class="paymentMethod === 'cash' ? 'border-amber-600 bg-amber-50/50 ring-2 ring-amber-500/20' : 'border-stone-200 hover:border-stone-300'">
                            <div class="flex items-center justify-between">
                                <span class="text-xl">💵</span>
                                <input type="radio" value="cash" x-model="paymentMethod" class="text-amber-600">
                            </div>
                            <span class="font-extrabold text-xs text-stone-900 mt-2">Tunai di Kasir</span>
                            <span class="text-[10px] text-stone-500 mt-0.5">Bayar di kasir setelah pesan</span>
                        </label>

                        <label class="flex flex-col p-3 rounded-2xl border transition cursor-pointer"
                               :class="paymentMethod === 'qris' ? 'border-amber-600 bg-amber-50/50 ring-2 ring-amber-500/20' : 'border-stone-200 hover:border-stone-300'">
                            <div class="flex items-center justify-between">
                                <span class="text-xl">📱</span>
                                <input type="radio" value="qris" x-model="paymentMethod" class="text-amber-600">
                            </div>
                            <span class="font-extrabold text-xs text-stone-900 mt-2">QRIS (Instan)</span>
                            <span class="text-[10px] text-stone-500 mt-0.5">Scan kode QR langsung di HP</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Drawer Footer (Submit Button) -->
            <div class="pt-4 border-t border-stone-100 space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-stone-500 font-semibold">Total Pembayaran</span>
                    <span class="font-black text-lg text-stone-900" x-text="'Rp ' + formatPrice(totalCartPrice())"></span>
                </div>

                <button @click="submitOrder()" :disabled="submitting || cart.length === 0"
                        class="w-full bg-amber-600 hover:bg-amber-700 disabled:opacity-50 text-white py-3.5 px-4 rounded-xl font-bold text-sm shadow-xl shadow-amber-600/30 transition flex items-center justify-center space-x-2">
                    <span x-show="!submitting">Konfirmasi & Kirim Pesanan</span>
                    <span x-show="submitting">Memproses Pesanan...</span>
                </button>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
function customerApp() {
    return {
        tableNumber: '{{ $table->table_number }}',
        tableToken: '{{ $table->table_token }}',
        activeCategory: 'all',
        modalOpen: false,
        cartDrawerOpen: false,
        submitting: false,
        selectedMenu: null,
        selectedOptions: {}, // { groupId: [optionId, ...] }
        itemNotes: '',
        itemQty: 1,
        customerName: '',
        paymentMethod: 'qris',
        cart: [],

        init() {
            // Restore cart from localStorage if exists for this table
            const savedCart = localStorage.getItem('kayla_cart_' + this.tableNumber);
            if (savedCart) {
                try {
                    this.cart = JSON.parse(savedCart);
                } catch(e) {
                    this.cart = [];
                }
            }
        },

        saveCart() {
            localStorage.setItem('kayla_cart_' + this.tableNumber, JSON.stringify(this.cart));
        },

        openModal(menu) {
            if (!menu.is_available) {
                alert('Maaf, menu ini sedang habis.');
                return;
            }

            this.selectedMenu = menu;
            this.selectedOptions = {};
            this.itemNotes = '';
            this.itemQty = 1;

            // Auto-select first option for required single-choice variation groups
            if (menu.variation_groups) {
                menu.variation_groups.forEach(group => {
                    if (group.is_required && !group.allow_multiple && group.options && group.options.length > 0) {
                        this.selectedOptions[group.id] = [group.options[0].id];
                    } else {
                        this.selectedOptions[group.id] = [];
                    }
                });
            }

            this.modalOpen = true;
        },

        isOptionSelected(group, optionId) {
            const list = this.selectedOptions[group.id] || [];
            return list.includes(optionId);
        },

        toggleOption(group, option) {
            if (!this.selectedOptions[group.id]) {
                this.selectedOptions[group.id] = [];
            }

            if (group.allow_multiple) {
                const idx = this.selectedOptions[group.id].indexOf(option.id);
                if (idx > -1) {
                    this.selectedOptions[group.id].splice(idx, 1);
                } else {
                    this.selectedOptions[group.id].push(option.id);
                }
            } else {
                this.selectedOptions[group.id] = [option.id];
            }
        },

        calculateCurrentItemTotal() {
            if (!this.selectedMenu) return 0;
            let unitPrice = parseFloat(this.selectedMenu.base_price);

            if (this.selectedMenu.variation_groups) {
                this.selectedMenu.variation_groups.forEach(group => {
                    const chosen = this.selectedOptions[group.id] || [];
                    group.options.forEach(opt => {
                        if (chosen.includes(opt.id)) {
                            unitPrice += parseFloat(opt.extra_price);
                        }
                    });
                });
            }

            return unitPrice * this.itemQty;
        },

        addToCart() {
            // Validasi required variation groups
            if (this.selectedMenu.variation_groups) {
                for (let group of this.selectedMenu.variation_groups) {
                    const chosen = this.selectedOptions[group.id] || [];
                    if (group.is_required && chosen.length === 0) {
                        alert('Silakan pilih salah satu opsi untuk: ' + group.name);
                        return;
                    }
                }
            }

            let unitPrice = parseFloat(this.selectedMenu.base_price);
            let optionIds = [];
            let optionLabels = [];

            if (this.selectedMenu.variation_groups) {
                this.selectedMenu.variation_groups.forEach(group => {
                    const chosen = this.selectedOptions[group.id] || [];
                    group.options.forEach(opt => {
                        if (chosen.includes(opt.id)) {
                            unitPrice += parseFloat(opt.extra_price);
                            optionIds.push(opt.id);
                            optionLabels.push(opt.name);
                        }
                    });
                });
            }

            this.cart.push({
                menu_id: this.selectedMenu.id,
                name: this.selectedMenu.name,
                unit_price: unitPrice,
                quantity: this.itemQty,
                subtotal: unitPrice * this.itemQty,
                special_notes: this.itemNotes.trim(),
                selected_options: optionIds,
                selected_options_labels: optionLabels,
            });

            this.saveCart();
            this.modalOpen = false;
        },

        removeCartItem(index) {
            this.cart.splice(index, 1);
            this.saveCart();
            if (this.cart.length === 0) {
                this.cartDrawerOpen = false;
            }
        },

        incrementCartItem(index) {
            this.cart[index].quantity++;
            this.cart[index].subtotal = this.cart[index].unit_price * this.cart[index].quantity;
            this.saveCart();
        },

        decrementCartItem(index) {
            if (this.cart[index].quantity > 1) {
                this.cart[index].quantity--;
                this.cart[index].subtotal = this.cart[index].unit_price * this.cart[index].quantity;
                this.saveCart();
            } else {
                this.removeCartItem(index);
            }
        },

        totalCartItemsCount() {
            return this.cart.reduce((sum, item) => sum + item.quantity, 0);
        },

        totalCartPrice() {
            return this.cart.reduce((sum, item) => sum + item.subtotal, 0);
        },

        formatPrice(val) {
            return new Intl.NumberFormat('id-ID').format(val || 0);
        },

        async submitOrder() {
            if (this.cart.length === 0) return;

            this.submitting = true;
            try {
                const response = await fetch('{{ route('customer.checkout') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        table_number: this.tableNumber,
                        table_token: this.tableToken,
                        customer_name: this.customerName,
                        payment_method: this.paymentMethod,
                        cart_items: this.cart,
                    })
                });

                const data = await response.json();

                if (response.ok && data.success) {
                    // Clear cart
                    this.cart = [];
                    localStorage.removeItem('kayla_cart_' + this.tableNumber);
                    window.location.href = data.redirect_url;
                } else {
                    alert(data.message || 'Gagal mengirim pesanan. Silakan periksa kembali.');
                }
            } catch (err) {
                alert('Terjadi kesalahan koneksi saat mengirim pesanan.');
            } finally {
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
