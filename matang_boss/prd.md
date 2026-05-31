📑 PRODUCT REQUIREMENTS DOCUMENT (PRD)
Nama Produk: Matang Boss
Versi Dokumen: 3.0 (Final Architecture & Specs)
Platform: Web Application (Responsive)
Tech Stack Utama: PHP (XAMPP), MySQL, Vanilla JS/AJAX, Python (AI API)
1. Ringkasan Eksekutif
Matang Boss adalah sistem informasi cerdas berbasis web yang memadukan Computer Vision (CNN) dan Rule-Based Expert System. Aplikasi ini berfungsi untuk mengklasifikasikan tingkat kematangan kelapa sawit, mendeteksi penyakit daun/buah beserta solusinya, mencatat geolokasi panen, serta menyediakan direktori tempat penjualan (RAMP/PKS) berbasis lokasi (LBS - Location Based Service).
2. Kebutuhan Antarmuka (UI/UX) & Prinsip Desain
Desain antarmuka difokuskan pada visibilitas status sistem, pencegahan error, dan feedback pengguna yang jelas.
•	Tema Visual:
o	Warna Primer: Hijau Sawit (#2E7D32 atau #4CAF50).
o	Warna Latar: Putih Bersih (#FFFFFF) dan Abu-abu Terang (#F3F4F6) untuk memisahkan konten card.
o	Tipografi: Sans-serif modern (Inter atau Roboto) untuk keterbacaan optimal.
•	Struktur Layout Utama:
o	Left Navigation Bar (Sidebar): Terletak di sisi kiri layar dengan latar belakang hijau gelap dan teks putih. Berisi menu: Dashboard, Scan Buah, Riwayat Panen, Direktori RAMP, Persetujuan Admin (Khusus Admin), Pengaturan, Keluar.
o	Main Content Area: Berada di sebelah kanan sidebar dengan latar belakang putih.
•	Responsivitas: Pada tampilan mobile, Left Sidebar disembunyikan dan diakses melalui hamburger menu.
3. Spesifikasi Fungsional & Alur Fitur (Modul)
Modul 1: AI Scanning & Auto-Geolocation (Kamera)
•	Akses Kamera: Menggunakan getUserMedia API via JavaScript. Tampilan kamera terintegrasi langsung di dalam halaman web.
•	Visual Feedback (Efek CNN):
o	Saat antarmuka kamera aktif, terdapat kotak fokus (bounding box) di tengah layar.
o	Saat tombol "Scan" ditekan, muncul scanning line hijau yang bergerak naik-turun disertai animasi persentase (0% - 100%) untuk memberikan umpan balik bahwa model AI sedang memproses.
•	Auto-Geolocation: Bersamaan dengan tombol "Scan" ditekan, JS Geolocation API mengambil latitude dan longitude pengguna di titik tersebut.
•	Integrasi AI: Gambar (Base64) dan koordinat dikirim via AJAX (fetch) ke endpoint Python lokal.
Modul 2: Sistem Pakar (Hasil & Penyakit)
•	Output AI (Dari endpoint Python ke UI):
o	Kematangan: Label (Mentah, Matang, Lewat Matang) + Confidence Score (%).
o	Deteksi Penyakit (Sistem Pakar):
	Jika penyakit == null: Tampilkan status "Buah/Daun Sehat".
	Jika terdeteksi (misal: Ganoderma), sistem memicu aturan (forward chaining) dan menampilkan: "Terdeteksi: [Nama Penyakit] | Solusi: [Tindakan teknis agronomis dari database]".
•	Penyimpanan: Data hasil klasifikasi, penyakit, dan lokasi otomatis dikirim ke PHP (simpan_scan.php) untuk disimpan ke tabel MySQL riwayat_scan.
Modul 3: Riwayat Panen (Peta & Pagination)
•	Tampilan: Format Card Layout (Foto thumbnail, Hasil, Tanggal).
•	Geolokasi Interaktif: Setiap card memiliki tombol "Lihat Peta". Jika diklik, akan memunculkan modal berisi peta Leaflet.js dengan penanda (pin) merah pada koordinat lokasi di mana foto tersebut diambil.
•	Optimasi: Menggunakan Pagination (10 data per halaman) dan Lazy Loading (gambar src hanya dimuat saat elemen masuk ke dalam viewport pengguna).
Modul 4: Direktori RAMP (Marketplace LBS)
•	Tampilan Petani:
o	Menampilkan daftar RAMP yang berstatus Approved.
o	Menghitung jarak dari lokasi pengguna saat ini ke lokasi RAMP menggunakan algoritma Haversine Formula.
o	Informasi yang tampil: Nama RAMP, Jarak (KM), Harga Jual (Rp/Kg), Tombol "Hubungi WhatsApp" (memicu wa.me/nomor), dan Tombol "Arahkan" (membuka Google Maps rute).
•	Pendaftaran RAMP (Pemilik RAMP):
o	Form pendaftaran: Nama RAMP, Koordinat (Bisa drag-and-drop pin di peta), No. WA, Harga awal.
o	Pencegahan Error: Pesan pop-up (SweetAlert): "Pendaftaran berhasil. RAMP Anda dalam status Tunda. Hubungi Admin di 0812-XXXX-XXXX untuk konfirmasi."
•	Manajemen Harga: Pemilik RAMP yang sudah disetujui memiliki akses ke panel untuk memperbarui kolom harga_jual harian.
Modul 5: Panel Admin
•	Fungsi: Mengelola entitas platform.
•	Persetujuan RAMP: Tabel daftar pendaftar RAMP baru. Terdapat tombol Action: Terima (Approve) atau Tolak (Reject) untuk mengubah status_approval.
4. Performa & Non-Fungsional
1.	Caching:
o	Menggunakan localStorage di browser untuk menyimpan data profil pengguna yang sedang login dan titik kordinat terakhir (menghindari request GPS berulang).
2.	Lazy Loading:
o	Diterapkan pada atribut <img loading="lazy" src="..."> pada halaman Riwayat.
3.	Algoritma Haversine (Penghitung Jarak):
Diimplementasikan pada level PHP (hitung_jarak.php) atau query MySQL secara langsung menggunakan formula matematis spasial:
$$a = \sin^2\left(\frac{\Delta\varphi}{2}\right) + \cos\varphi_1 \cdot \cos\varphi_2 \cdot \sin^2\left(\frac{\Delta\lambda}{2}\right)$$
$$c = 2 \cdot \text{atan2}\left(\sqrt{a}, \sqrt{1-a}\right)$$
$$d = R \cdot c$$
5. Arsitektur Komunikasi (XAMPP & AI)
Karena menggunakan environment lokal (XAMPP), sistem beroperasi dalam dua service yang saling berkomunikasi:
•	Service 1 (Web & DB): Apache (Port 80) & MySQL (Port 3306).
o	Menjalankan skrip PHP untuk antarmuka, login, session, CRUD RAMP, dan manipulasi database via phpMyAdmin.
•	Service 2 (AI Engine): Python Flask/FastAPI (Port 5000).
o	Berjalan di background. Mengandung model model_sawit.h5.
o	Menerima HTTP POST request (berisi image Base64) dari JavaScript frontend, melakukan inferensi, dan mengembalikan JSON.
6. Desain Basis Data (MySQL / phpMyAdmin Schema)
Berikut adalah struktur tabel (ERD) yang harus dibuat di phpMyAdmin:
Tabel users
•	id_user (INT, PK, A_I)
•	username (VARCHAR 50)
•	password (VARCHAR 255, di-hash dengan password_hash)
•	role (ENUM: 'petani', 'admin', 'pemilik_ramp')
•	created_at (TIMESTAMP)
Tabel scan_history
•	id_scan (INT, PK, A_I)
•	id_user (INT, FK -> users.id_user)
•	file_gambar (VARCHAR 255, contoh: uploads/scan_1680.jpg)
•	hasil_kematangan (VARCHAR 50)
•	confidence_score (DECIMAL 5,2)
•	nama_penyakit (VARCHAR 100, NULL diperbolehkan)
•	solusi_penyakit (TEXT, NULL diperbolehkan)
•	latitude (DECIMAL 10,8)
•	longitude (DECIMAL 11,8)
•	timestamp (DATETIME)
Tabel ramp_directory
•	id_ramp (INT, PK, A_I)
•	id_user (INT, FK -> users.id_user) -- Relasi ke pemilik ramp
•	nama_ramp (VARCHAR 100)
•	alamat_lengkap (TEXT)
•	latitude (DECIMAL 10,8)
•	longitude (DECIMAL 11,8)
•	no_whatsapp (VARCHAR 20)
•	harga_jual (INT)
•	status_approval (ENUM: 'pending', 'approved', 'rejected') Default: 'pending'
•	last_updated (TIMESTAMP)
Tabel expert_rules (Sistem Pakar)
•	id_rule (INT, PK, A_I)
•	gejala_atau_penyakit (VARCHAR 100)
•	rekomendasi_tindakan (TEXT)
7. Standar Pengiriman Kode (Deliverables)
Saat mengeksekusi dokumen ini, AI/Developer diwajibkan untuk membangun:
1.	File SQL: matangboss_db.sql (berisi DDL dari tabel di atas).
2.	API Python: app.py (Script Flask untuk memuat model H5/TFLite dan endpoint /predict).
3.	Frontend/Backend PHP: Direktori XAMPP (htdocs/matangboss/) yang mencakup implementasi index.php, sidebar.php, kamera.js, dan file koneksi MySQL (db.php).

