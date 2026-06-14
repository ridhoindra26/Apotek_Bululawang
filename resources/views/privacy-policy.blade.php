@extends('layout.guest')

@section('title', 'Kebijakan Privasi')

@section('content')
<div class="min-h-screen bg-slate-50 py-8">
    <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-200">
            <div class="border-b border-slate-200 px-6 py-6 sm:px-8">
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">
                    Kebijakan Privasi
                </h1>
                <p class="mt-2 text-sm text-slate-500">
                    Terakhir diperbarui: 14 Juni 2026
                </p>
            </div>

            <div class="space-y-8 px-6 py-8 text-sm leading-7 text-slate-700 sm:px-8">
                <section>
                    <p>
                        Kebijakan Privasi ini menjelaskan bagaimana Apotek Bululawang
                        mengumpulkan, menggunakan, menyimpan, dan melindungi data pengguna
                        dalam penggunaan sistem internal, website, serta layanan digital
                        yang terhubung dengan operasional Apotek Bululawang.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        1. Data yang Kami Kumpulkan
                    </h2>

                    <p>
                        Kami dapat mengumpulkan beberapa jenis data yang diperlukan untuk
                        mendukung operasional apotek, antara lain:
                    </p>

                    <ul class="mt-3 list-disc space-y-2 pl-6">
                        <li>Data pengguna, seperti nama, email, nomor telepon, username, dan peran akun.</li>
                        <li>Data karyawan, seperti nama, cabang kerja, jabatan, absensi, jam masuk, jam keluar, dan riwayat kehadiran.</li>
                        <li>Data foto yang digunakan untuk kebutuhan validasi absensi atau dokumentasi internal.</li>
                        <li>Data transaksi penjualan, pembelian, penerimaan barang, stok obat, supplier, dan laporan operasional.</li>
                        <li>Data pelanggan yang diberikan secara sukarela untuk kebutuhan layanan, riwayat transaksi, pengingat, atau pelayanan kesehatan.</li>
                        <li>Data komunikasi seperti nomor WhatsApp, pesan layanan, atau referensi transaksi apabila sistem terhubung dengan layanan komunikasi pihak ketiga.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        2. Penggunaan Data
                    </h2>

                    <p>
                        Data yang dikumpulkan digunakan untuk tujuan berikut:
                    </p>

                    <ul class="mt-3 list-disc space-y-2 pl-6">
                        <li>Mengelola akun pengguna dan hak akses sistem.</li>
                        <li>Mengelola data karyawan, absensi, jadwal, dan saldo waktu kerja.</li>
                        <li>Mengelola stok obat, supplier, penerimaan barang, transaksi, dan laporan apotek.</li>
                        <li>Meningkatkan keamanan, akurasi, dan konsistensi data operasional.</li>
                        <li>Mengirimkan informasi layanan, notifikasi, atau komunikasi yang relevan kepada pelanggan atau karyawan.</li>
                        <li>Memenuhi kebutuhan administrasi, audit internal, dan kepatuhan terhadap peraturan yang berlaku.</li>
                    </ul>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        3. Penyimpanan dan Perlindungan Data
                    </h2>

                    <p>
                        Kami berupaya menjaga keamanan data dengan menerapkan pembatasan akses,
                        autentikasi pengguna, pengelolaan hak akses berdasarkan peran, serta
                        mekanisme teknis lain yang sesuai dengan kebutuhan sistem.
                    </p>

                    <p class="mt-3">
                        Data hanya dapat diakses oleh pihak yang berwenang sesuai dengan tugas
                        dan tanggung jawabnya di lingkungan Apotek Bululawang.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        4. Berbagi Data dengan Pihak Ketiga
                    </h2>

                    <p>
                        Kami tidak menjual, menyewakan, atau memperdagangkan data pribadi pengguna
                        kepada pihak ketiga.
                    </p>

                    <p class="mt-3">
                        Data dapat dibagikan kepada layanan pihak ketiga hanya apabila diperlukan
                        untuk menjalankan fitur sistem, seperti layanan hosting, penyimpanan data,
                        autentikasi, komunikasi WhatsApp/API, pengiriman notifikasi, atau layanan
                        pendukung operasional lainnya.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        5. Penggunaan Layanan WhatsApp atau API Pihak Ketiga
                    </h2>

                    <p>
                        Apabila sistem Apotek Bululawang terhubung dengan WhatsApp Business API,
                        Meta API, Google API, atau layanan pihak ketiga lainnya, data yang diproses
                        hanya digunakan untuk mendukung kebutuhan layanan, komunikasi, autentikasi,
                        integrasi sistem, dan operasional apotek.
                    </p>

                    <p class="mt-3">
                        Penggunaan data pada layanan pihak ketiga juga dapat tunduk pada kebijakan
                        privasi dan ketentuan layanan dari masing-masing penyedia layanan tersebut.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        6. Hak Pengguna
                    </h2>

                    <p>
                        Pengguna dapat menghubungi Apotek Bululawang untuk meminta informasi,
                        pembaruan, koreksi, atau penghapusan data pribadi sesuai dengan kebijakan
                        internal dan ketentuan hukum yang berlaku.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        7. Retensi Data
                    </h2>

                    <p>
                        Data disimpan selama masih diperlukan untuk tujuan operasional, administrasi,
                        pelaporan, keamanan, atau selama diwajibkan oleh ketentuan hukum yang berlaku.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        8. Perubahan Kebijakan Privasi
                    </h2>

                    <p>
                        Kami dapat memperbarui Kebijakan Privasi ini dari waktu ke waktu sesuai
                        dengan perubahan sistem, layanan, atau kebutuhan operasional. Setiap perubahan
                        akan ditampilkan pada halaman ini.
                    </p>
                </section>

                <section>
                    <h2 class="mb-3 text-lg font-semibold text-slate-900">
                        9. Kontak
                    </h2>

                    <p>
                        Untuk pertanyaan terkait Kebijakan Privasi atau pengelolaan data pribadi,
                        silakan hubungi:
                    </p>

                    <div class="mt-4 rounded-xl bg-slate-50 p-4 ring-1 ring-slate-200">
                        <p>
                            <span class="font-semibold text-slate-900">Apotek Bululawang</span>
                        </p>
                        <p class="mt-1">
                            Email:
                            <a href="mailto:admin_bululawang1@apotekbululawang.com" class="font-medium text-blue-600 hover:text-blue-700">
                                admin_bululawang1@apotekbululawang.com
                            </a>
                        </p>
                        <p class="mt-1">
                            WhatsApp:
                            <a href="https://wa.me/6282257423118" target="_blank" class="font-medium text-blue-600 hover:text-blue-700">
                                6282257423118
                            </a>
                        </p>
                        <p class="mt-1">
                            Alamat: Bululawang, Malang, Jawa Timur, Indonesia
                        </p>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
@endsection