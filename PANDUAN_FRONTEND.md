# 📘 Panduan Pembuatan Frontend — SDG7Learn

> Dokumen ini ditujukan untuk anggota tim yang bertugas membuat UI/UX frontend.
> Semua endpoint backend **sudah selesai dan berjalan**. Tugas frontend adalah membuat tampilan web dan menghubungkannya ke API.

---

## 1. Tentang Proyek

**SDG7Learn** adalah platform edukasi web tentang **energi bersih dan terjangkau (SDG 7)**. Aplikasi menyediakan:

- **Artikel** edukasi tentang energi bersih
- **Kuis interaktif** untuk menguji pemahaman
- **Forum diskusi** antar pengguna
- **Panel admin** untuk mengelola semua konten

### Peran Pengguna

| Peran | Bisa Apa |
|---|---|
| **Publik** (belum login) | Baca artikel |
| **User** (mahasiswa) | Baca artikel, kerjakan kuis, lihat riwayat skor, buat/balas postingan forum |
| **Admin** | Semua fitur user + kelola artikel, kelola kuis, moderasi forum, lihat data pengguna |

---

## 2. Teknologi & Arsitektur

### Stack Backend (Sudah Jadi)

- **Framework:** Laravel 13 (PHP)
- **Auth:** Laravel Sanctum (token-based)
- **Database:** SQLite (dev), bisa ganti MySQL/PostgreSQL di production
- **API style:** REST JSON, semua endpoint di bawah `/api/v1/`

### Stack Frontend (Yang Harus Dibuat)

Frontend menggunakan **Blade template** bawaan Laravel (file `.blade.php` di `resources/views/`). Styling dan interaktivitas bisa pakai:

- **CSS** (vanilla atau framework seperti Bootstrap/Tailwind)
- **JavaScript** vanilla atau library ringan (Alpine.js, jQuery, dll.)
- **Vite** sudah terkonfigurasi di proyek untuk bundling asset

### Cara Kerja Koneksi Frontend ↔ Backend

Frontend memanggil API memakai **fetch()** atau **axios** dari JavaScript. Semua data dikirim/diterima dalam format **JSON**.

```
Browser  →  GET /api/v1/artikel  →  Laravel Controller  →  JSON Response
Browser  ←  { success: true, data: [...] }  ←
```

---

## 3. Cara Menjalankan Proyek

```bash
# 1. Install dependensi (jika belum)
composer install

# 2. Salin .env
cp .env.example .env
php artisan key:generate

# 3. Jalankan migrasi database
php artisan migrate

# 4. Jalankan server development
php artisan serve
# Server berjalan di http://127.0.0.1:8000
```

---

## 4. Struktur File Frontend

Semua file frontend ada di dalam folder `resources/`:

```
resources/
├── views/              ← File Blade (.blade.php)
│   ├── layouts/        ← Layout utama (header, sidebar, footer)
│   │   └── app.blade.php
│   ├── auth/           ← Halaman login & register
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── artikel/        ← Halaman artikel
│   │   ├── index.blade.php
│   │   └── show.blade.php
│   ├── kuis/           ← Halaman kuis
│   │   ├── index.blade.php
│   │   ├── show.blade.php  (mengerjakan kuis)
│   │   └── hasil.blade.php
│   ├── forum/          ← Halaman forum
│   │   ├── index.blade.php
│   │   ├── show.blade.php
│   │   └── create.blade.php
│   ├── profil/         ← Halaman profil user
│   │   └── index.blade.php
│   ├── admin/          ← Panel admin
│   │   ├── dashboard.blade.php
│   │   ├── artikel/
│   │   ├── kuis/
│   │   ├── forum/
│   │   └── pengguna/
│   └── welcome.blade.php  ← Landing page
├── css/
│   └── app.css
└── js/
    └── app.js
```

> Struktur di atas adalah **rekomendasi**. Kamu bebas menyesuaikan.

---

## 5. Autentikasi (Login/Register/Logout)

### Alur Autentikasi

```
1. User mengisi form login → POST /api/v1/auth/login
2. Server mengembalikan access_token + refresh_token
3. Simpan token di localStorage/sessionStorage
4. Setiap request ke endpoint yang butuh login, sertakan header:
     Authorization: Bearer <access_token>
5. Jika token expired (401) → POST /api/v1/auth/refresh
6. Logout → POST /api/v1/auth/logout → hapus token dari storage
```

### Contoh JavaScript Fetch

```javascript
// === LOGIN ===
async function login(email, password) {
  const res = await fetch('/api/v1/auth/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const json = await res.json();

  if (json.success) {
    localStorage.setItem('access_token', json.data.access_token);
    localStorage.setItem('refresh_token', json.data.refresh_token);
    localStorage.setItem('user', JSON.stringify(json.data.pengguna));
    // Redirect berdasarkan role
    if (json.data.pengguna.role === 'admin') {
      window.location.href = '/admin/dashboard';
    } else {
      window.location.href = '/';
    }
  } else {
    alert(json.message); // "Email atau password salah"
  }
}

// === REGISTER ===
async function register(data) {
  const res = await fetch('/api/v1/auth/register', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      nama: data.nama,
      email: data.email,
      password: data.password,
      password_confirmation: data.passwordConfirm,
      pekerjaan: data.pekerjaan,   // opsional
      usia: data.usia              // opsional (angka 10-100)
    })
  });
  const json = await res.json();
  // Handle sama seperti login (json.data punya access_token juga)
}

// === HELPER: Request dengan Token ===
async function apiFetch(url, options = {}) {
  const token = localStorage.getItem('access_token');
  const headers = {
    'Content-Type': 'application/json',
    ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
    ...options.headers
  };

  const res = await fetch(url, { ...options, headers });

  // Auto-refresh jika token expired
  if (res.status === 401) {
    const refreshed = await refreshToken();
    if (refreshed) {
      headers['Authorization'] = `Bearer ${localStorage.getItem('access_token')}`;
      return fetch(url, { ...options, headers });
    } else {
      localStorage.clear();
      window.location.href = '/login';
    }
  }

  return res;
}

// === REFRESH TOKEN ===
async function refreshToken() {
  const rt = localStorage.getItem('refresh_token');
  if (!rt) return false;

  const res = await fetch('/api/v1/auth/refresh', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ refresh_token: rt })
  });
  const json = await res.json();

  if (json.success) {
    localStorage.setItem('access_token', json.data.access_token);
    localStorage.setItem('refresh_token', json.data.refresh_token);
    return true;
  }
  return false;
}

// === LOGOUT ===
async function logout() {
  await apiFetch('/api/v1/auth/logout', {
    method: 'POST',
    body: JSON.stringify({
      refresh_token: localStorage.getItem('refresh_token')
    })
  });
  localStorage.clear();
  window.location.href = '/login';
}
```

---

## 6. Daftar Halaman yang Harus Dibuat

### 6.1 Halaman Publik (Tanpa Login)

#### 🏠 Landing Page (`/`)
- Hero section tentang SDG7 / energi bersih
- Highlight artikel terbaru (ambil dari `GET /api/v1/artikel?limit=3`)
- Tombol CTA: "Mulai Belajar", "Login", "Register"

#### 📰 Daftar Artikel (`/artikel`)
- Daftar kartu artikel: judul, ringkasan, sumber, gambar, tanggal
- Fitur search (query `q`), pagination
- **API:** `GET /api/v1/artikel?page=1&limit=10&q=energi&sort=-tanggal`

#### 📰 Detail Artikel (`/artikel/{id}`)
- Judul, isi lengkap, sumber, gambar, tanggal
- **API:** `GET /api/v1/artikel/{id}`

#### 🔑 Login (`/login`)
- Form: email, password
- **API:** `POST /api/v1/auth/login`

#### 📝 Register (`/register`)
- Form: nama, email, password, konfirmasi password, pekerjaan (opsional), usia (opsional)
- **API:** `POST /api/v1/auth/register`

---

### 6.2 Halaman User (Harus Login, role = user)

#### 📝 Daftar Kuis (`/kuis`)
- Kartu kuis: judul, deskripsi, jumlah pertanyaan, total poin
- Badge "Sudah Dikerjakan" + skor terbaik jika pernah mengerjakan
- **API:** `GET /api/v1/kuis?page=1&limit=10&q=&sort=-dibuat_pada`

#### 📝 Kerjakan Kuis (`/kuis/{id}`)
- Detail kuis + daftar pertanyaan dengan opsi pilihan ganda
- Setiap pertanyaan punya beberapa opsi (radio button), user pilih satu
- Tombol "Kirim Jawaban"
- **API detail:** `GET /api/v1/kuis/{id}`
- **API kirim jawaban:** `POST /api/v1/kuis/{id}/percobaan`
  ```json
  {
    "jawaban": [
      { "pertanyaan_id": 1, "opsi_id": 3 },
      { "pertanyaan_id": 2, "opsi_id": 7 },
      { "pertanyaan_id": 3, "opsi_id": null }
    ]
  }
  ```
  > `opsi_id: null` = pertanyaan tidak dijawab

#### 📊 Riwayat Hasil Kuis (`/hasil-kuis`)
- Tabel/kartu: nama kuis, skor, skor maks, persentase, tanggal
- Klik untuk lihat detail tinjauan
- **API:** `GET /api/v1/hasil-kuis?sort=-dikerjakan_pada`

#### 📊 Detail Hasil Kuis (`/hasil-kuis/{id}`)
- Ringkasan: skor, persentase, jumlah benar
- Tinjauan per pertanyaan: teks pertanyaan, opsi yang dipilih, opsi benar, benar/salah, penjelasan
- **API:** `GET /api/v1/hasil-kuis/{id}`

#### 💬 Daftar Forum (`/forum`)
- Daftar postingan: judul, ringkasan, penulis (nama, role), jumlah balasan, tanggal
- Tombol "Buat Postingan Baru"
- **API:** `GET /api/v1/forum/postingan?page=1&q=&sort=-tanggal`

#### 💬 Detail Postingan (`/forum/{id}`)
- Judul, isi lengkap, penulis, tanggal
- Daftar balasan di bawahnya (dengan paginasi)
- Form untuk menulis balasan
- **API detail:** `GET /api/v1/forum/postingan/{id}`
- **API balasan:** `GET /api/v1/forum/postingan/{id}/balasan?sort=tanggal`
- **API kirim balasan:** `POST /api/v1/forum/postingan/{id}/balasan`
  ```json
  { "isi": "Konten balasan..." }
  ```

#### 💬 Buat Postingan (`/forum/buat`)
- Form: judul, isi
- **API:** `POST /api/v1/forum/postingan`
  ```json
  { "judul": "Judul postingan", "isi": "Isi postingan..." }
  ```

#### 👤 Profil Saya (`/profil`)
- Tampilkan: nama, email, pekerjaan, usia, role, tanggal dibuat
- Form edit profil (nama, pekerjaan, usia)
- Form ganti password (password lama, password baru, konfirmasi)
- **API lihat:** `GET /api/v1/profil`
- **API update:** `PUT /api/v1/profil`
  ```json
  { "nama": "Nama Baru", "pekerjaan": "Mahasiswa", "usia": 21 }
  ```
- **API ganti password:** `PUT /api/v1/profil/password`
  ```json
  {
    "password_lama": "oldpass123",
    "password_baru": "newpass456",
    "password_baru_confirmation": "newpass456"
  }
  ```

---

### 6.3 Halaman Admin (Harus Login, role = admin)

#### 📊 Dashboard Admin (`/admin/dashboard`)
- Ringkasan statistik: jumlah artikel, jumlah kuis, jumlah user, jumlah postingan
- (Data bisa diambil dari masing-masing endpoint list dengan `limit=1` untuk cek `meta.total_items`)

#### 📰 Kelola Artikel (`/admin/artikel`)
- Tabel artikel (ambil dari `GET /api/v1/artikel`)
- Tombol: Tambah, Edit, Hapus
- **Tambah:** `POST /api/v1/admin/artikel`
  ```json
  {
    "judul": "Judul Artikel",
    "isi": "Isi lengkap artikel...",
    "sumber": "https://sumber.com",
    "gambar_url": "https://gambar.com/img.jpg"
  }
  ```
- **Edit:** `PUT /api/v1/admin/artikel/{id}` (body sama)
- **Hapus:** `DELETE /api/v1/admin/artikel/{id}`

#### 📝 Kelola Kuis (`/admin/kuis`)
- Tabel kuis: judul, deskripsi, jumlah pertanyaan, total poin
- **Daftar:** `GET /api/v1/admin/kuis`
- **Detail + kunci jawaban:** `GET /api/v1/admin/kuis/{id}`
  - Response berisi `pertanyaan[].penjelasan` dan `opsi[].benar` (info kunci jawaban)
- **Buat kuis + pertanyaan sekaligus:** `POST /api/v1/admin/kuis`
  ```json
  {
    "judul": "Kuis Energi Surya",
    "deskripsi": "Menguji pemahaman tentang panel surya",
    "pertanyaan": [
      {
        "teks": "Apa sumber energi panel surya?",
        "poin": 10,
        "penjelasan": "Panel surya mengubah cahaya matahari...",
        "opsi": [
          { "teks": "Matahari", "benar": true },
          { "teks": "Angin", "benar": false },
          { "teks": "Air", "benar": false }
        ]
      }
    ]
  }
  ```
  > ⚠️ Setiap pertanyaan **HARUS** punya tepat 1 opsi dengan `benar: true`
- **Edit judul/deskripsi:** `PUT /api/v1/admin/kuis/{id}`
- **Hapus kuis:** `DELETE /api/v1/admin/kuis/{id}`
- **Tambah pertanyaan:** `POST /api/v1/admin/kuis/{id}/pertanyaan`
- **Edit pertanyaan:** `PUT /api/v1/admin/kuis/{id}/pertanyaan/{pertanyaan_id}`
- **Hapus pertanyaan:** `DELETE /api/v1/admin/kuis/{id}/pertanyaan/{pertanyaan_id}`
- **Rekap hasil user:** `GET /api/v1/admin/kuis/{id}/hasil?sort=-dikerjakan_pada`

#### 💬 Moderasi Forum (`/admin/forum`)
- Daftar postingan + info penulis lengkap (nama, role, pekerjaan, usia)
- Tombol hapus postingan dan hapus balasan (moderasi)
- **Daftar:** `GET /api/v1/admin/forum/postingan`
- **Hapus postingan:** `DELETE /api/v1/admin/forum/postingan/{id}`
- **Hapus balasan:** `DELETE /api/v1/admin/forum/balasan/{id}`

#### 👥 Kelola Pengguna (`/admin/pengguna`)
- Tabel user: nama, email, pekerjaan, usia, role, tanggal daftar
- Search by nama/email (query `q`)
- Detail user + ringkasan aktivitas
- **Daftar:** `GET /api/v1/admin/pengguna?q=&sort=-dibuat_pada`
- **Detail:** `GET /api/v1/admin/pengguna/{id}`
  - Response berisi `aktivitas.jumlah_postingan`, `aktivitas.jumlah_balasan`, `aktivitas.jumlah_kuis_diikuti`

---

## 7. Format Response API

Semua response dari API mengikuti format konsisten:

### Sukses (satu objek)
```json
{
  "success": true,
  "message": "Pesan dalam bahasa Indonesia",
  "data": { ... }
}
```

### Sukses (daftar berpaginasi)
```json
{
  "success": true,
  "message": "Daftar artikel berhasil diambil",
  "data": [ ... ],
  "meta": {
    "page": 1,
    "limit": 10,
    "total_items": 42,
    "total_pages": 5
  }
}
```

### Sukses tanpa data (DELETE, logout)
```json
{
  "success": true,
  "message": "Artikel berhasil dihapus",
  "data": null
}
```

### Error
```json
{
  "success": false,
  "message": "Pesan error untuk ditampilkan ke user",
  "error": {
    "code": "NAMA_KODE",
    "details": []
  }
}
```

### Error Validasi (422)
```json
{
  "success": false,
  "message": "Data yang dikirim tidak valid",
  "error": {
    "code": "VALIDATION_ERROR",
    "details": [
      { "field": "email", "message": "Format email tidak valid" },
      { "field": "usia", "message": "Usia harus antara 10 dan 100" }
    ]
  }
}
```

### Kode Error yang Perlu Di-handle di Frontend

| Kode Error | HTTP Status | Artinya | Yang Harus Dilakukan di UI |
|---|---|---|---|
| `VALIDATION_ERROR` | 422 | Input tidak valid | Tampilkan pesan per-field di bawah form input |
| `UNAUTHENTICATED` | 401 | Belum login / token salah | Redirect ke halaman login |
| `TOKEN_EXPIRED` | 401 | Token kedaluwarsa | Auto-refresh token, ulangi request |
| `INVALID_REFRESH_TOKEN` | 401 | Refresh token tidak valid | Hapus storage, redirect ke login |
| `FORBIDDEN` | 403 | Tidak punya akses | Tampilkan pesan "Akses ditolak" |
| `NOT_FOUND` | 404 | Data tidak ditemukan | Tampilkan halaman 404 |
| `CONFLICT` | 409 | Bentrok (misal email sudah terdaftar) | Tampilkan pesan error |
| `INTERNAL_SERVER_ERROR` | 500 | Error server | Tampilkan "Terjadi kesalahan, coba lagi" |

---

## 8. Paginasi di Frontend

Semua endpoint daftar mendukung parameter:

| Parameter | Default | Range |
|---|---|---|
| `page` | 1 | ≥ 1 |
| `limit` | 10 | 1–50 |
| `q` | — | String pencarian (maks 100 karakter) |
| `sort` | beda per endpoint | Awalan `-` = descending |

### Nilai `sort` per Endpoint

| Endpoint | Nilai sort yang valid | Default |
|---|---|---|
| Artikel | `tanggal`, `-tanggal` | `-tanggal` |
| Kuis | `dibuat_pada`, `-dibuat_pada` | `-dibuat_pada` |
| Hasil Kuis | `dikerjakan_pada`, `-dikerjakan_pada` | `-dikerjakan_pada` |
| Forum Postingan | `tanggal`, `-tanggal` | `-tanggal` |
| Forum Balasan | `tanggal`, `-tanggal` | `tanggal` (terlama dulu) |
| Admin Pengguna | `dibuat_pada`, `-dibuat_pada` | `-dibuat_pada` |
| Admin Hasil Kuis | `dikerjakan_pada`, `-dikerjakan_pada`, `skor`, `-skor` | `-dikerjakan_pada` |

### Contoh Implementasi Paginasi

```javascript
async function loadArtikel(page = 1, search = '') {
  const params = new URLSearchParams({
    page: page,
    limit: 10,
    sort: '-tanggal'
  });
  if (search) params.set('q', search);

  const res = await fetch(`/api/v1/artikel?${params}`);
  const json = await res.json();

  // Render data
  renderArtikelList(json.data);

  // Render pagination buttons
  renderPagination(json.meta);
  // meta.page, meta.total_pages, meta.total_items, meta.limit
}
```

---

## 9. Routing Frontend (routes/web.php)

File `routes/web.php` perlu ditambahi route untuk setiap halaman. Semua route mengembalikan view Blade — data diambil via JavaScript fetch ke API.

```php
<?php
use Illuminate\Support\Facades\Route;

// ===== Halaman Publik =====
Route::get('/', fn () => view('welcome'));
Route::get('/artikel', fn () => view('artikel.index'));
Route::get('/artikel/{id}', fn () => view('artikel.show'));
Route::get('/login', fn () => view('auth.login'));
Route::get('/register', fn () => view('auth.register'));

// ===== Halaman User (perlu login) =====
Route::get('/kuis', fn () => view('kuis.index'));
Route::get('/kuis/{id}', fn () => view('kuis.show'));
Route::get('/hasil-kuis', fn () => view('kuis.hasil'));
Route::get('/hasil-kuis/{id}', fn () => view('kuis.hasil-detail'));
Route::get('/forum', fn () => view('forum.index'));
Route::get('/forum/buat', fn () => view('forum.create'));
Route::get('/forum/{id}', fn () => view('forum.show'));
Route::get('/profil', fn () => view('profil.index'));

// ===== Halaman Admin =====
Route::get('/admin/dashboard', fn () => view('admin.dashboard'));
Route::get('/admin/artikel', fn () => view('admin.artikel.index'));
Route::get('/admin/kuis', fn () => view('admin.kuis.index'));
Route::get('/admin/kuis/{id}', fn () => view('admin.kuis.show'));
Route::get('/admin/forum', fn () => view('admin.forum.index'));
Route::get('/admin/pengguna', fn () => view('admin.pengguna.index'));
Route::get('/admin/pengguna/{id}', fn () => view('admin.pengguna.show'));
```

> **Catatan:** Proteksi akses (harus login / harus admin) dilakukan di **sisi JavaScript** — cek apakah ada token di localStorage. Jika tidak ada, redirect ke `/login`. Jika role bukan admin, redirect ke home.

---

## 10. Glosarium Istilah Bahasa Indonesia di API

Semua field API menggunakan bahasa Indonesia. Berikut referensi:

| Field API | Artinya |
|---|---|
| `nama` | Nama lengkap user |
| `pekerjaan` | Pekerjaan (opsional) |
| `usia` | Usia dalam tahun |
| `dibuat_pada` | Tanggal dibuat (ISO 8601 UTC) |
| `diperbarui_pada` | Tanggal terakhir diperbarui |
| `judul` | Judul (artikel/kuis/postingan) |
| `isi` | Konten/body teks |
| `ringkasan` | 150 karakter pertama dari `isi` |
| `sumber` | Sumber referensi artikel |
| `gambar_url` | URL gambar sampul |
| `tanggal` | Waktu dibuat |
| `deskripsi` | Deskripsi kuis |
| `pertanyaan` | Array pertanyaan kuis |
| `urutan` | Nomor urut pertanyaan (mulai 1) |
| `teks` | Teks pertanyaan/opsi |
| `poin` | Nilai poin pertanyaan |
| `penjelasan` | Penjelasan jawaban benar (khusus admin & tinjauan) |
| `opsi` | Pilihan jawaban |
| `benar` | Boolean, apakah opsi ini benar (khusus admin) |
| `skor` | Jumlah poin yang diperoleh |
| `skor_maks` | Total poin maksimal kuis |
| `persentase` | (skor / skor_maks) × 100 |
| `jumlah_benar` | Jumlah jawaban benar |
| `jumlah_pertanyaan` | Total pertanyaan dalam kuis |
| `sudah_dikerjakan` | Boolean, apakah user pernah kerjakan kuis ini |
| `skor_terbaik` | Skor tertinggi user di kuis ini |
| `dikerjakan_pada` | Tanggal pengerjaan kuis |
| `tinjauan` | Snapshot review per-pertanyaan setelah kerjakan kuis |
| `opsi_dipilih_id` | ID opsi yang dipilih user |
| `opsi_benar_id` | ID opsi yang benar |
| `poin_diperoleh` | Poin yang didapat (= poin jika benar, 0 jika salah) |
| `penulis` | Objek penulis postingan/balasan |
| `postingan` | Postingan forum |
| `balasan` | Balasan/reply di forum |
| `jumlah_balasan` | Jumlah reply pada postingan |
| `pengguna` | Data user (di rekap admin) |
| `aktivitas` | Ringkasan aktivitas user (postingan, balasan, kuis) |

---

## 11. Daftar Lengkap 36 Endpoint API

| # | Method | URL | Akses | Deskripsi |
|---|---|---|---|---|
| 1 | `POST` | `/api/v1/auth/register` | Publik | Register user baru |
| 2 | `POST` | `/api/v1/auth/login` | Publik | Login |
| 3 | `POST` | `/api/v1/auth/refresh` | Publik | Refresh token |
| 4 | `POST` | `/api/v1/auth/logout` | Login | Logout |
| 5 | `GET` | `/api/v1/profil` | Login | Lihat profil sendiri |
| 6 | `PUT` | `/api/v1/profil` | Login | Update profil |
| 7 | `PUT` | `/api/v1/profil/password` | Login | Ganti password |
| 8 | `GET` | `/api/v1/artikel` | Publik | Daftar artikel |
| 9 | `GET` | `/api/v1/artikel/{id}` | Publik | Detail artikel |
| 10 | `GET` | `/api/v1/kuis` | Login | Daftar kuis |
| 11 | `GET` | `/api/v1/kuis/{id}` | Login | Detail kuis (tanpa kunci) |
| 12 | `POST` | `/api/v1/kuis/{id}/percobaan` | User | Kirim jawaban kuis |
| 13 | `GET` | `/api/v1/hasil-kuis` | User | Riwayat hasil kuis |
| 14 | `GET` | `/api/v1/hasil-kuis/{id}` | Login | Detail hasil kuis |
| 15 | `GET` | `/api/v1/forum/postingan` | Login | Daftar postingan |
| 16 | `POST` | `/api/v1/forum/postingan` | Login | Buat postingan |
| 17 | `GET` | `/api/v1/forum/postingan/{id}` | Login | Detail postingan |
| 18 | `GET` | `/api/v1/forum/postingan/{id}/balasan` | Login | Daftar balasan |
| 19 | `POST` | `/api/v1/forum/postingan/{id}/balasan` | Login | Kirim balasan |
| 20 | `POST` | `/api/v1/admin/artikel` | Admin | Tambah artikel |
| 21 | `PUT` | `/api/v1/admin/artikel/{id}` | Admin | Edit artikel |
| 22 | `DELETE` | `/api/v1/admin/artikel/{id}` | Admin | Hapus artikel |
| 23 | `GET` | `/api/v1/admin/kuis` | Admin | Daftar kuis (admin) |
| 24 | `GET` | `/api/v1/admin/kuis/{id}` | Admin | Detail kuis + kunci |
| 25 | `POST` | `/api/v1/admin/kuis` | Admin | Buat kuis |
| 26 | `PUT` | `/api/v1/admin/kuis/{id}` | Admin | Edit kuis |
| 27 | `DELETE` | `/api/v1/admin/kuis/{id}` | Admin | Hapus kuis |
| 28 | `POST` | `/api/v1/admin/kuis/{id}/pertanyaan` | Admin | Tambah pertanyaan |
| 29 | `PUT` | `/api/v1/admin/kuis/{id}/pertanyaan/{pid}` | Admin | Edit pertanyaan |
| 30 | `DELETE` | `/api/v1/admin/kuis/{id}/pertanyaan/{pid}` | Admin | Hapus pertanyaan |
| 31 | `GET` | `/api/v1/admin/kuis/{id}/hasil` | Admin | Rekap hasil kuis |
| 32 | `GET` | `/api/v1/admin/forum/postingan` | Admin | Rekap forum |
| 33 | `DELETE` | `/api/v1/admin/forum/postingan/{id}` | Admin | Hapus postingan |
| 34 | `DELETE` | `/api/v1/admin/forum/balasan/{id}` | Admin | Hapus balasan |
| 35 | `GET` | `/api/v1/admin/pengguna` | Admin | Daftar pengguna |
| 36 | `GET` | `/api/v1/admin/pengguna/{id}` | Admin | Detail pengguna |

---

## 12. Catatan Penting untuk AI Eksternal

1. **Backend sudah 100% jadi.** Jangan buat controller, model, atau migration baru. Fokus hanya pada file di `resources/views/`, `resources/css/`, `resources/js/`, dan `routes/web.php`.

2. **Semua data diambil via JavaScript fetch ke `/api/v1/...`**, bukan melalui Blade server-side rendering. Blade hanya dipakai sebagai template HTML kosong, lalu JavaScript mengisi data secara dinamis.

3. **Token disimpan di `localStorage`**. Setiap request ke endpoint yang butuh login, sertakan header `Authorization: Bearer <token>`.

4. **Field menggunakan bahasa Indonesia** (lihat glosarium di bagian 10). Jangan ubah nama field.

5. **Paginasi** menggunakan query `page` dan `limit`. Response berisi objek `meta` dengan info halaman.

6. **Validasi error** dari server berformat `error.details[]` berisi `{ field, message }`. Tampilkan pesan error per-field di bawah input yang bermasalah.

7. **Desain harus responsif** (mobile + desktop). Tema terkait energi bersih / SDG 7 (warna hijau, kuning, biru).

8. **Base URL API: `http://127.0.0.1:8000`** saat development (atau gunakan relative URL `/api/v1/...` langsung).
