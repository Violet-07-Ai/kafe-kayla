# Product Requirements Document (PRD)
## Web Pemesanan Kafe Berbasis QR Code

- **Versi:** 1.0
- **Target:** Proyek kuliah / demonstrasi
- **Teknologi utama:** Laravel dan MySQL
- **Lingkungan:** Server lokal pada jaringan Wi-Fi kafe

---

## 1. Masalah

Proses pemesanan kafe secara manual mengharuskan pelanggan memanggil staf untuk melihat menu dan menyampaikan pesanan. Hal ini dapat menambah beban kerja staf dan meningkatkan risiko kesalahan pencatatan, terutama pada variasi menu, jumlah item, dan nomor meja.

Kafe membutuhkan sistem pemesanan berbasis web yang memungkinkan pelanggan memesan secara mandiri melalui QR code unik pada setiap meja. Pesanan dikelola kasir sebelum diteruskan ke dapur, sementara pelanggan dapat memantau status pesanan melalui browser.

Sistem juga perlu menyediakan pencatatan pembayaran tunai dan simulasi pembayaran QRIS melalui payment gateway sandbox, serta laporan transaksi bagi admin.

## 2. Pengguna

### 2.1 Pelanggan
- Mengakses web melalui browser HP tanpa membuat akun.
- Memindai QR code meja dan melihat menu digital.
- Memilih menu, variasi, jumlah, dan catatan khusus.
- Mengirim pesanan, melakukan pembayaran, dan memantau status pesanan.

### 2.2 Admin
- Mengelola menu, variasi, ketersediaan, meja, dan QR code.
- Mengelola akun staf dan hak akses.
- Melihat laporan penjualan dan transaksi.

### 2.3 Kasir
- Melihat pesanan masuk.
- Mengonfirmasi atau membatalkan pesanan.
- Mencatat pembayaran tunai atau memverifikasi status pembayaran QRIS.
- Meneruskan pesanan yang sudah lunas ke dapur.
- Memperbarui status pesanan hingga selesai.

### 2.4 Staf dapur
- Melihat pesanan yang sudah diteruskan kasir.
- Melihat item, jumlah, variasi, dan catatan pelanggan.
- Tidak dapat mengubah status pesanan atau pembayaran.

## 3. Fitur

### 3.1 Fitur pelanggan
- **QR code unik per meja:** sistem mengenali nomor meja secara otomatis.
- **Menu digital:** menampilkan nama, foto, deskripsi, harga, dan ketersediaan produk.
- **Variasi menu:** mendukung pilihan seperti ukuran, tingkat gula, dan es, termasuk perbedaan harga jika berlaku.
- **Keranjang pesanan:** pelanggan dapat menambah, mengurangi, atau menghapus item sebelum mengirim pesanan.
- **Catatan khusus:** pelanggan dapat menambahkan instruksi untuk makanan atau minuman.
- **Pemesanan tanpa login:** pelanggan tidak perlu membuat akun.
- **Nomor pesanan unik:** setiap pesanan memiliki identitas unik, meskipun berasal dari meja yang sama.
- **Pelacakan status:** pelanggan dapat melihat perkembangan pesanan melalui halaman web.
- **Pembayaran:** mendukung pembayaran tunai di kasir dan QRIS melalui payment gateway sandbox.

### 3.2 Fitur kasir
- Melihat daftar pesanan masuk beserta waktu, nomor pesanan, nomor meja, item, variasi, catatan, dan total harga.
- Mengonfirmasi atau menolak pesanan dengan alasan yang tercatat.
- Mengubah status pesanan sesuai alur yang ditentukan.
- Mencatat pembayaran tunai.
- Memeriksa status pembayaran QRIS dari payment gateway sandbox.
- Meneruskan pesanan ke dapur hanya setelah pembayaran dinyatakan lunas.
- Menandai pesanan siap disajikan dan sudah disajikan.
- Memanggil nomor pesanan ketika pesanan siap disajikan.

### 3.3 Fitur staf dapur
- Melihat daftar pesanan yang sudah diteruskan kasir.
- Melihat rincian item, jumlah, variasi, dan catatan khusus.
- Memuat ulang daftar untuk melihat pesanan terbaru.
- Tidak memiliki akses untuk mengubah pesanan, pembayaran, menu, atau status pesanan.

### 3.4 Fitur admin
- **Manajemen menu:** CRUD untuk nama, deskripsi, foto, harga, kategori, dan informasi produk.
- **Manajemen variasi:** mengatur pilihan ukuran, gula, es, dan harga terkait.
- **Ketersediaan menu:** menandai menu tersedia atau habis.
- **Manajemen meja:** membuat, mengedit, menonaktifkan, dan mengelola QR code unik setiap meja.
- **Manajemen akun staf:** mengelola akun dan hak akses admin, kasir, serta staf dapur.
- **Laporan penjualan:** ringkasan harian dan bulanan.
- **Laporan pembayaran:** rincian pembayaran tunai dan QRIS.
- **Menu terlaris:** daftar menu berdasarkan jumlah pesanan.
- **Detail transaksi:** melihat data pesanan dan status pembayarannya.

### 3.5 Alur utama sistem
1. Pelanggan memindai QR code unik dan sistem mengenali meja.
2. Pelanggan memilih menu, variasi, jumlah, dan catatan, lalu mengirim pesanan.
3. Pesanan masuk dengan status **Menunggu konfirmasi kasir**.
4. Kasir memeriksa lalu mengonfirmasi atau membatalkan pesanan.
5. Pelanggan membayar melalui QRIS atau di kasir.
6. QRIS harus terverifikasi melalui payment gateway; pembayaran tunai harus dicatat kasir.
7. Setelah pembayaran lunas, kasir meneruskan pesanan ke dapur.
8. Staf dapur melihat pesanan dan menyiapkannya.
9. Kasir memperbarui status menjadi **Siap disajikan**, lalu **Sudah disajikan / selesai**.
10. Nomor pesanan dipanggil staf saat pesanan siap disajikan.

#### Status pesanan
1. **Menunggu konfirmasi kasir**
2. **Dikonfirmasi kasir**
3. **Diteruskan ke dapur**
4. **Siap disajikan**
5. **Sudah disajikan / selesai**
6. **Dibatalkan**

Status pembayaran disimpan terpisah dari status pesanan, misalnya **menunggu pembayaran**, **lunas**, **gagal**, atau **kedaluwarsa**. Pesanan yang dibatalkan tidak otomatis dianggap lunas atau menghasilkan pengembalian dana.

## 4. Di Luar Cakupan (Out of Scope)

Fitur berikut tidak termasuk dalam versi pertama:
- Aplikasi Android atau iOS khusus.
- Pemesanan takeaway, pesan antar, dan reservasi meja sebelum kedatangan.
- Registrasi dan login pelanggan.
- Pengelolaan beberapa cabang kafe.
- Manajemen stok bahan baku, resep, dan pengurangan persediaan otomatis.
- Notifikasi melalui WhatsApp, SMS, atau email.
- Ekspor laporan ke Excel atau CSV.
- Program loyalitas, poin, voucher, dan diskon kompleks.
- Integrasi pembayaran produksi dengan dana nyata.
- Pengelolaan akuntansi, penggajian, dan pemasok.
- Akses publik dari luar jaringan kafe.

## 5. Batasan

### 5.1 Teknologi dan infrastruktur
- Backend menggunakan **Laravel**.
- Database menggunakan **MySQL**.
- Frontend menggunakan HTML, CSS, JavaScript, dan antarmuka responsif berbasis browser.
- Server aplikasi berada di jaringan lokal kafe.
- Pelanggan dan staf mengakses web melalui Wi-Fi kafe.
- Perangkat staf berupa HP atau tablet.
- Sistem dirancang untuk satu lokasi dan satu server lokal.

### 5.2 Pembayaran
- Integrasi QRIS menggunakan mode sandbox dari penyedia yang mendukung kebutuhan integrasi.
- Status lunas QRIS harus berasal dari konfirmasi penyedia yang dapat diverifikasi, bukan hanya dari tampilan halaman pelanggan.
- Pembayaran tunai dianggap lunas setelah dicatat oleh kasir.
- Pesanan hanya boleh diteruskan ke dapur jika pembayaran lunas.
- Pembayaran produksi dan penyelesaian dana tidak termasuk dalam target prototipe.

### 5.3 Koneksi dan operasional
- Koneksi internet tersedia untuk layanan eksternal.
- Sesuai kebijakan operasional, sistem boleh berhenti sementara saat internet terputus.
- Karena server berada di LAN, putusnya internet tidak otomatis menghentikan komunikasi lokal. Perilaku aplikasi saat koneksi putus perlu ditentukan dan diuji secara eksplisit.
- Payment gateway memerlukan mekanisme callback atau konfirmasi yang sesuai dengan lingkungan pengembangan. Server lokal mungkin memerlukan tunnel pengembangan atau mekanisme pengujian callback dari penyedia.
- Pembaruan status pelanggan harus cukup cepat untuk demonstrasi. Implementasi dapat menggunakan polling berkala atau teknologi pembaruan real-time.

### 5.4 Keamanan dan data
- Akses admin, kasir, dan staf dapur dibedakan berdasarkan peran.
- Password staf disimpan menggunakan hashing yang aman.
- Harga dan total transaksi dihitung ulang di server, bukan dipercaya dari browser pelanggan.
- Callback pembayaran harus diverifikasi dan diproses secara idempoten agar notifikasi berulang tidak mencatat transaksi dua kali.
- Setiap pesanan memiliki nomor unik dan keterkaitan meja yang jelas.

## 6. Kriteria Penerimaan (Acceptance Criteria)

### 6.1 Pemesanan pelanggan
- [ ] Pelanggan dapat membuka menu melalui QR code unik tanpa login.
- [ ] Sistem mengenali nomor meja dengan benar.
- [ ] Pelanggan dapat memilih item, variasi, jumlah, dan catatan khusus.
- [ ] Total harga dihitung dengan benar berdasarkan harga produk dan variasi.
- [ ] Setiap pesanan memperoleh nomor unik, termasuk pesanan berbeda dari meja yang sama.
- [ ] Menu yang ditandai habis tidak dapat dipesan.

### 6.2 Kasir dan pembayaran
- [ ] Pesanan baru tampil pada dashboard kasir dengan rincian yang benar.
- [ ] Kasir dapat mengonfirmasi atau membatalkan pesanan sesuai aturan sistem.
- [ ] Pembayaran tunai hanya berstatus lunas setelah dicatat kasir.
- [ ] Pembayaran QRIS hanya berstatus lunas setelah konfirmasi sandbox yang valid terverifikasi.
- [ ] Pesanan yang belum lunas tidak dapat diteruskan ke dapur.
- [ ] Callback pembayaran yang diterima berulang tidak menyebabkan transaksi ganda.

### 6.3 Dapur dan status pesanan
- [ ] Staf dapur hanya dapat melihat pesanan yang telah diteruskan kasir.
- [ ] Staf dapur tidak dapat mengubah status pesanan atau pembayaran.
- [ ] Kasir dapat memperbarui status pesanan sesuai alur yang diizinkan.
- [ ] Perubahan status tampil pada halaman pelacakan pelanggan.
- [ ] Nomor pesanan dapat dikenali saat staf memanggil pesanan yang siap disajikan.

### 6.4 Admin dan laporan
- [ ] Admin dapat membuat, mengedit, dan menghapus atau menonaktifkan menu sesuai aturan referensi data.
- [ ] Admin dapat mengatur variasi menu dan ketersediaannya.
- [ ] Admin dapat membuat dan mengelola QR code meja.
- [ ] Admin dapat membuat akun staf dan menetapkan perannya.
- [ ] Laporan menampilkan ringkasan harian dan bulanan sesuai transaksi yang tercatat.
- [ ] Laporan membedakan pembayaran tunai dan QRIS serta menampilkan status transaksi.
- [ ] Daftar menu terlaris sesuai jumlah pesanan dalam periode yang dipilih.

### 6.5 Teknis dan keamanan
- [ ] Antarmuka dapat digunakan melalui browser HP dan tablet.
- [ ] Pengguna tanpa izin ditolak saat mengakses fitur atau endpoint terlarang.
- [ ] Server memvalidasi harga, total transaksi, status pesanan, dan status pembayaran.
- [ ] Data pesanan dan transaksi tetap konsisten ketika halaman dimuat ulang.
- [ ] Perilaku sistem ketika internet terputus telah diuji dan sesuai kebijakan operasional.

Kriteria penerimaan dinyatakan lulus apabila semua skenario yang relevan berhasil diuji pada lingkungan demonstrasi. Integrasi QRIS sandbox harus dibuktikan dengan transaksi uji, termasuk skenario gagal atau kedaluwarsa, bukan hanya dengan menampilkan gambar QRIS pada halaman.

---

## Catatan implementasi

Detail yang belum ditetapkan, seperti penyedia payment gateway, format nomor pesanan, dan interval pembaruan status, dapat diputuskan dalam spesifikasi teknis tanpa mengubah tujuan utama produk.
