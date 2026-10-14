<?php

namespace Database\Seeders;

use App\Models\Artikel;
use App\Models\Balasan;
use App\Models\HasilKuis;
use App\Models\Kuis;
use App\Models\Opsi;
use App\Models\Pertanyaan;
use App\Models\Postingan;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ─────────────────────────────────────────
        // 1. USERS
        // ─────────────────────────────────────────
        $admin = User::create([
            'nama'      => 'Admin SDG7',
            'email'     => 'admin@sdg7learn.com',
            'password'  => Hash::make('admin123'),
            'pekerjaan' => 'Administrator',
            'usia'      => 30,
            'role'      => 'admin',
        ]);

        $cristian = User::create([
            'nama'      => 'Cristian Reynaldi',
            'email'     => 'cristian@gmail.com',
            'password'  => Hash::make('password123'),
            'pekerjaan' => 'Mahasiswa',
            'usia'      => 21,
            'role'      => 'user',
        ]);

        $deco = User::create([
            'nama'      => 'Deco Prasetya',
            'email'     => 'deco@gmail.com',
            'password'  => Hash::make('password123'),
            'pekerjaan' => 'Mahasiswa',
            'usia'      => 21,
            'role'      => 'user',
        ]);

        $daffa = User::create([
            'nama'      => 'Daffa Hafist Atha Kuncoro',
            'email'     => 'daffa@gmail.com',
            'password'  => Hash::make('password123'),
            'pekerjaan' => 'Mahasiswa',
            'usia'      => 20,
            'role'      => 'user',
        ]);

        $adistya = User::create([
            'nama'      => 'Muhammad Adistya Rafif Rasyendra',
            'email'     => 'adistya@gmail.com',
            'password'  => Hash::make('password123'),
            'pekerjaan' => 'Mahasiswa',
            'usia'      => 21,
            'role'      => 'user',
        ]);

        $this->command->info('✅ 5 pengguna berhasil ditambahkan (1 admin + 4 user)');

        // ─────────────────────────────────────────
        // 2. ARTIKEL
        // ─────────────────────────────────────────
        $artikelData = [
            [
                'judul'      => 'Apa Itu Energi Terbarukan? Panduan Lengkap untuk Pemula',
                'isi'        => "Energi terbarukan adalah energi yang berasal dari sumber daya alam yang dapat dipulihkan secara alami dalam skala waktu manusia. Berbeda dengan bahan bakar fosil yang membutuhkan jutaan tahun untuk terbentuk, sumber energi terbarukan seperti matahari, angin, air, dan panas bumi terus tersedia selama bumi masih berputar.\n\nMenurut International Renewable Energy Agency (IRENA), kapasitas energi terbarukan global telah mencapai lebih dari 3.000 GW pada tahun 2022. Angka ini menunjukkan pertumbuhan yang luar biasa dalam dua dekade terakhir.\n\nJenis-jenis energi terbarukan meliputi:\n\n1. Energi Surya — Memanfaatkan radiasi matahari melalui panel fotovoltaik atau konsentrator surya.\n2. Energi Angin — Mengubah energi kinetik angin menjadi listrik melalui turbin.\n3. Energi Air (Hidropower) — Memanfaatkan aliran atau jatuhan air untuk menggerakkan turbin.\n4. Energi Panas Bumi (Geotermal) — Memanfaatkan panas dari dalam bumi.\n5. Biomassa — Menggunakan bahan organik sebagai sumber energi.\n\nTransisi menuju energi terbarukan bukan hanya tentang teknologi, tetapi juga tentang keadilan energi. SDG 7 menekankan bahwa setiap orang berhak mendapatkan akses energi yang terjangkau, andal, dan berkelanjutan.",
                'sumber'     => 'https://www.irena.org/publications',
                'gambar_url' => 'https://images.unsplash.com/photo-1509391366360-2e959784a276?w=800',
            ],
            [
                'judul'      => 'Panel Surya di Indonesia: Potensi Besar yang Belum Termanfaatkan',
                'isi'        => "Indonesia, sebagai negara tropis yang terletak di garis khatulistiwa, memiliki potensi energi surya yang sangat besar. Rata-rata radiasi matahari di Indonesia mencapai 4,8 kWh per meter persegi per hari — salah satu yang tertinggi di dunia.\n\nNamun ironisnya, pemanfaatan energi surya di Indonesia baru mencapai kurang dari 0,05% dari total potensi yang ada. Berdasarkan data Kementerian ESDM, kapasitas terpasang PLTS di Indonesia baru sekitar 270 MWp dari potensi teknis sebesar 207,8 GWp.\n\nBeberapa tantangan utama dalam pengembangan panel surya di Indonesia:\n\n- Biaya investasi awal yang masih tinggi\n- Kurangnya insentif fiskal yang memadai\n- Keterbatasan infrastruktur jaringan listrik di daerah terpencil\n- Minimnya kesadaran masyarakat tentang manfaat energi surya\n\nPemerintah Indonesia telah menetapkan target bauran energi terbarukan sebesar 23% pada tahun 2025 melalui Perpres No. 22 Tahun 2017. Panel surya atap (rooftop solar) menjadi salah satu solusi yang paling menjanjikan untuk mempercepat pencapaian target ini.\n\nDengan harga panel surya yang terus menurun — turun hingga 90% dalam 10 tahun terakhir — kini semakin banyak rumah tangga dan bisnis yang mulai memasang panel surya di atap mereka.",
                'sumber'     => 'https://www.esdm.go.id/id/media-center/arsip-berita',
                'gambar_url' => 'https://images.unsplash.com/photo-1508514177221-188b1cf16e9d?w=800',
            ],
            [
                'judul'      => 'Energi Angin: Turbin Modern yang Mengubah Masa Depan',
                'isi'        => "Energi angin telah digunakan manusia selama ribuan tahun, mulai dari kincir angin tradisional di Belanda hingga turbin angin modern yang menjulang tinggi di laut lepas. Saat ini, energi angin menjadi salah satu sumber energi terbarukan dengan pertumbuhan tercepat di dunia.\n\nSebuah turbin angin modern dapat menghasilkan listrik hingga 15 MW — cukup untuk memasok kebutuhan listrik sekitar 13.000 rumah tangga. Di Denmark, energi angin sudah menyumbang lebih dari 50% total produksi listrik negara tersebut.\n\nIndonesia sendiri memiliki potensi energi angin yang signifikan, terutama di wilayah Nusa Tenggara Timur, Sulawesi Selatan, dan pesisir selatan Jawa. Kecepatan angin rata-rata di daerah-daerah ini mencapai 5-7 m/s, yang cukup memadai untuk operasi turbin angin.\n\nPembangkit Listrik Tenaga Bayu (PLTB) Sidrap di Sulawesi Selatan, dengan kapasitas 75 MW, merupakan pembangkit listrik tenaga angin terbesar di Asia Tenggara. Proyek ini membuktikan bahwa Indonesia memiliki kemampuan untuk mengembangkan energi angin skala besar.\n\nTeknologi turbin angin terus berkembang. Turbin offshore (lepas pantai) generasi terbaru bahkan mampu beroperasi di laut dalam dengan kedalaman lebih dari 60 meter, membuka potensi energi angin yang sebelumnya tidak dapat dijangkau.",
                'sumber'     => 'https://gwec.net/global-wind-report-2023/',
                'gambar_url' => 'https://images.unsplash.com/photo-1532601224476-15c79f2f7a51?w=800',
            ],
            [
                'judul'      => 'SDG 7: Memahami Tujuan Energi Bersih untuk Semua',
                'isi'        => "Sustainable Development Goal 7 (SDG 7) atau Tujuan Pembangunan Berkelanjutan ke-7 bertajuk \"Energi Bersih dan Terjangkau\" (Affordable and Clean Energy). Tujuan ini diadopsi oleh semua negara anggota PBB pada tahun 2015 sebagai bagian dari Agenda 2030.\n\nSDG 7 memiliki lima target utama:\n\n7.1 — Pada tahun 2030, menjamin akses universal terhadap layanan energi yang terjangkau, andal, dan modern.\n7.2 — Pada tahun 2030, meningkatkan secara substansial pangsa energi terbarukan dalam bauran energi global.\n7.3 — Pada tahun 2030, menggandakan laju perbaikan efisiensi energi secara global.\n7.a — Pada tahun 2030, memperkuat kerja sama internasional untuk memfasilitasi akses terhadap riset dan teknologi energi bersih.\n7.b — Pada tahun 2030, memperluas infrastruktur dan meningkatkan teknologi untuk menyediakan layanan energi modern dan berkelanjutan di negara berkembang.\n\nSaat ini, masih ada sekitar 675 juta orang di dunia yang tidak memiliki akses terhadap listrik, dan sekitar 2,3 miliar orang masih bergantung pada bahan bakar tradisional yang tidak efisien untuk memasak.\n\nPencapaian SDG 7 sangat penting karena energi menjadi fondasi bagi pencapaian hampir semua SDG lainnya — mulai dari penghapusan kemiskinan (SDG 1) hingga aksi iklim (SDG 13).",
                'sumber'     => 'https://sdgs.un.org/goals/goal7',
                'gambar_url' => 'https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?w=800',
            ],
            [
                'judul'      => 'Efisiensi Energi: Langkah Sederhana yang Berdampak Besar',
                'isi'        => "Efisiensi energi sering disebut sebagai \"bahan bakar pertama\" karena merupakan cara tercepat dan termurah untuk mengurangi emisi karbon dan menghemat biaya energi. Menurut International Energy Agency (IEA), perbaikan efisiensi energi dapat memenuhi lebih dari 40% target pengurangan emisi yang diperlukan untuk membatasi pemanasan global di bawah 2°C.\n\nBeberapa langkah sederhana yang bisa dilakukan setiap orang:\n\n🏠 Di Rumah:\n- Gunakan lampu LED yang menggunakan 75% lebih sedikit energi dibanding lampu pijar\n- Matikan peralatan elektronik saat tidak digunakan (jangan standby)\n- Gunakan AC pada suhu 24-26°C untuk keseimbangan kenyamanan dan efisiensi\n- Manfaatkan cahaya dan ventilasi alami semaksimal mungkin\n\n🏢 Di Kantor/Sekolah:\n- Terapkan kebijakan paperless untuk mengurangi penggunaan printer\n- Optimalkan pengaturan pendingin ruangan sesuai jumlah penghuni\n- Gunakan sensor gerak untuk pencahayaan di area yang jarang digunakan\n\n🚗 Transportasi:\n- Gunakan transportasi umum atau carpooling\n- Pertimbangkan kendaraan listrik atau hybrid\n- Jalan kaki atau bersepeda untuk jarak dekat\n\nDengan menerapkan langkah-langkah ini, rata-rata rumah tangga dapat menghemat 20-30% dari total tagihan listriknya per bulan.",
                'sumber'     => 'https://www.iea.org/topics/energy-efficiency',
                'gambar_url' => 'https://images.unsplash.com/photo-1497435334941-8c899ee9e8e9?w=800',
            ],
            [
                'judul'      => 'Energi Panas Bumi Indonesia: Kekayaan di Bawah Kaki Kita',
                'isi'        => "Indonesia memiliki sekitar 40% cadangan panas bumi (geotermal) dunia, menjadikannya negara dengan potensi geotermal terbesar di planet ini. Terletak di Cincin Api Pasifik dengan lebih dari 300 titik panas bumi yang telah teridentifikasi, Indonesia memiliki potensi geotermal sebesar 23,9 GW.\n\nHingga 2023, kapasitas terpasang pembangkit listrik tenaga panas bumi (PLTP) di Indonesia telah mencapai sekitar 2.356 MW, menjadikannya negara dengan kapasitas geotermal terpasang terbesar kedua di dunia setelah Amerika Serikat.\n\nKeunggulan energi geotermal:\n- Beroperasi 24/7 (tidak bergantung cuaca seperti surya atau angin)\n- Emisi karbon sangat rendah (rata-rata 122 g CO₂/kWh, jauh di bawah batubara)\n- Membutuhkan lahan yang relatif kecil\n- Umur operasi pembangkit sangat panjang (25-30 tahun)\n\nBeberapa PLTP terkenal di Indonesia:\n- Kamojang (Garut, Jawa Barat) — PLTP pertama di Indonesia, beroperasi sejak 1983\n- Wayang Windu (Pangalengan, Jawa Barat) — 227 MW\n- Sarulla (Tapanuli Utara, Sumatera Utara) — 330 MW, salah satu terbesar di dunia\n\nPengembangan geotermal di Indonesia memang menghadapi tantangan, terutama karena banyak sumber panas bumi berada di kawasan hutan lindung. Namun pemerintah terus berupaya menyeimbangkan antara kebutuhan energi dan pelestarian lingkungan.",
                'sumber'     => 'https://www.esdm.go.id/id/media-center/arsip-berita',
                'gambar_url' => 'https://images.unsplash.com/photo-1569163139394-de4e5f43e5ca?w=800',
            ],
        ];

        foreach ($artikelData as $index => $data) {
            $artikel = Artikel::create($data);
            // Geser tanggal supaya artikel punya urutan waktu berbeda
            $artikel->created_at = now()->subDays(count($artikelData) - $index);
            $artikel->updated_at = $artikel->created_at;
            $artikel->save();
        }

        $this->command->info('✅ ' . count($artikelData) . ' artikel berhasil ditambahkan');

        // ─────────────────────────────────────────
        // 3. KUIS + PERTANYAAN + OPSI
        // ─────────────────────────────────────────

        // --- Kuis 1: Dasar Energi Terbarukan ---
        $kuis1 = Kuis::create([
            'judul'    => 'Dasar-Dasar Energi Terbarukan',
            'deskripsi' => 'Kuis ini menguji pemahamanmu tentang konsep dasar energi terbarukan, jenis-jenisnya, dan perannya dalam kehidupan sehari-hari.',
        ]);

        $pertanyaan1 = [
            [
                'teks' => 'Manakah yang termasuk sumber energi terbarukan?',
                'poin' => 10,
                'penjelasan' => 'Energi surya (matahari) adalah sumber energi terbarukan karena matahari menyediakan energi secara terus-menerus dan tidak akan habis dalam waktu dekat. Batu bara, gas alam, dan minyak bumi adalah bahan bakar fosil yang tidak terbarukan.',
                'opsi' => [
                    ['teks' => 'Batu bara', 'benar' => false],
                    ['teks' => 'Energi surya', 'benar' => true],
                    ['teks' => 'Gas alam', 'benar' => false],
                    ['teks' => 'Minyak bumi', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Apa singkatan dari SDG?',
                'poin' => 10,
                'penjelasan' => 'SDG adalah Sustainable Development Goals (Tujuan Pembangunan Berkelanjutan) yang ditetapkan oleh PBB pada tahun 2015. Terdiri dari 17 tujuan yang ingin dicapai pada tahun 2030.',
                'opsi' => [
                    ['teks' => 'Standard Development Goals', 'benar' => false],
                    ['teks' => 'Sustainable Development Goals', 'benar' => true],
                    ['teks' => 'Social Development Guidelines', 'benar' => false],
                    ['teks' => 'Strategic Development Growth', 'benar' => false],
                ],
            ],
            [
                'teks' => 'SDG nomor berapa yang membahas tentang energi bersih dan terjangkau?',
                'poin' => 10,
                'penjelasan' => 'SDG 7 berjudul "Affordable and Clean Energy" atau "Energi Bersih dan Terjangkau". Tujuan ini bertarget menjamin akses universal terhadap layanan energi yang terjangkau, andal, dan modern pada tahun 2030.',
                'opsi' => [
                    ['teks' => 'SDG 5', 'benar' => false],
                    ['teks' => 'SDG 7', 'benar' => true],
                    ['teks' => 'SDG 11', 'benar' => false],
                    ['teks' => 'SDG 13', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Berapa persen target bauran energi terbarukan Indonesia pada tahun 2025?',
                'poin' => 10,
                'penjelasan' => 'Melalui Perpres No. 22 Tahun 2017 tentang Rencana Umum Energi Nasional (RUEN), pemerintah Indonesia menetapkan target bauran energi terbarukan sebesar 23% pada tahun 2025 dan 31% pada tahun 2050.',
                'opsi' => [
                    ['teks' => '10%', 'benar' => false],
                    ['teks' => '15%', 'benar' => false],
                    ['teks' => '23%', 'benar' => true],
                    ['teks' => '50%', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Apa keunggulan utama energi panas bumi dibandingkan energi surya dan angin?',
                'poin' => 10,
                'penjelasan' => 'Energi panas bumi dapat beroperasi 24 jam sehari, 7 hari seminggu tanpa bergantung pada cuaca. Berbeda dengan panel surya yang hanya bekerja saat matahari bersinar atau turbin angin yang membutuhkan angin.',
                'opsi' => [
                    ['teks' => 'Lebih murah instalasinya', 'benar' => false],
                    ['teks' => 'Dapat beroperasi 24/7 tanpa bergantung cuaca', 'benar' => true],
                    ['teks' => 'Tidak membutuhkan lahan', 'benar' => false],
                    ['teks' => 'Tidak menghasilkan emisi sama sekali', 'benar' => false],
                ],
            ],
        ];

        $this->seedPertanyaan($kuis1, $pertanyaan1);

        // --- Kuis 2: Energi Surya ---
        $kuis2 = Kuis::create([
            'judul'     => 'Mengenal Energi Surya',
            'deskripsi' => 'Uji pengetahuanmu tentang panel surya, cara kerja sel fotovoltaik, dan potensi energi matahari di Indonesia.',
        ]);

        $pertanyaan2 = [
            [
                'teks' => 'Komponen utama yang mengubah cahaya matahari menjadi listrik pada panel surya disebut?',
                'poin' => 10,
                'penjelasan' => 'Sel fotovoltaik (photovoltaic cell) adalah komponen utama panel surya yang mengubah energi cahaya matahari langsung menjadi energi listrik melalui efek fotovoltaik.',
                'opsi' => [
                    ['teks' => 'Konduktor', 'benar' => false],
                    ['teks' => 'Sel fotovoltaik', 'benar' => true],
                    ['teks' => 'Generator', 'benar' => false],
                    ['teks' => 'Transformator', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Berapa rata-rata radiasi matahari di Indonesia per hari?',
                'poin' => 10,
                'penjelasan' => 'Indonesia yang terletak di garis khatulistiwa menerima rata-rata radiasi matahari sekitar 4,8 kWh per meter persegi per hari, menjadikannya salah satu negara dengan potensi energi surya terbesar di dunia.',
                'opsi' => [
                    ['teks' => '2,5 kWh/m²', 'benar' => false],
                    ['teks' => '4,8 kWh/m²', 'benar' => true],
                    ['teks' => '7,2 kWh/m²', 'benar' => false],
                    ['teks' => '10 kWh/m²', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Berapa persen penurunan harga panel surya dalam 10 tahun terakhir?',
                'poin' => 10,
                'penjelasan' => 'Harga panel surya telah turun secara drastis, mencapai penurunan hingga 90% dalam dekade terakhir. Hal ini membuat energi surya semakin kompetitif dibandingkan sumber energi konvensional.',
                'opsi' => [
                    ['teks' => '30%', 'benar' => false],
                    ['teks' => '50%', 'benar' => false],
                    ['teks' => '70%', 'benar' => false],
                    ['teks' => '90%', 'benar' => true],
                ],
            ],
            [
                'teks' => 'Jenis panel surya manakah yang paling umum digunakan di rumah tangga?',
                'poin' => 10,
                'penjelasan' => 'Panel surya monokristalin (monocrystalline) adalah yang paling umum digunakan di rumah tangga karena memiliki efisiensi tertinggi (15-22%) dan membutuhkan ruang yang lebih kecil dibandingkan jenis lainnya.',
                'opsi' => [
                    ['teks' => 'Monokristalin', 'benar' => true],
                    ['teks' => 'Amorfus', 'benar' => false],
                    ['teks' => 'Konsentrator', 'benar' => false],
                    ['teks' => 'Termal', 'benar' => false],
                ],
            ],
        ];

        $this->seedPertanyaan($kuis2, $pertanyaan2);

        // --- Kuis 3: Efisiensi Energi ---
        $kuis3 = Kuis::create([
            'judul'     => 'Kuis Efisiensi Energi Sehari-hari',
            'deskripsi' => 'Seberapa paham kamu tentang cara menghemat energi dalam kehidupan sehari-hari? Yuk uji pengetahuanmu!',
        ]);

        $pertanyaan3 = [
            [
                'teks' => 'Lampu jenis apa yang paling hemat energi?',
                'poin' => 10,
                'penjelasan' => 'Lampu LED menggunakan hingga 75% lebih sedikit energi dibandingkan lampu pijar konvensional dan bertahan 25 kali lebih lama. LED adalah pilihan paling efisien saat ini.',
                'opsi' => [
                    ['teks' => 'Lampu pijar', 'benar' => false],
                    ['teks' => 'Lampu neon', 'benar' => false],
                    ['teks' => 'Lampu LED', 'benar' => true],
                    ['teks' => 'Lampu halogen', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Suhu AC yang disarankan untuk keseimbangan antara kenyamanan dan efisiensi energi adalah?',
                'poin' => 10,
                'penjelasan' => 'Suhu 24-26°C adalah pengaturan yang direkomendasikan untuk pendingin ruangan. Setiap penurunan 1°C dapat meningkatkan konsumsi energi AC sekitar 6-8%.',
                'opsi' => [
                    ['teks' => '16-18°C', 'benar' => false],
                    ['teks' => '20-22°C', 'benar' => false],
                    ['teks' => '24-26°C', 'benar' => true],
                    ['teks' => '28-30°C', 'benar' => false],
                ],
            ],
            [
                'teks' => 'Berapa persen rata-rata penghematan listrik jika rumah tangga menerapkan efisiensi energi secara konsisten?',
                'poin' => 10,
                'penjelasan' => 'Dengan menerapkan langkah-langkah efisiensi energi sederhana seperti mematikan peralatan saat tidak digunakan, menggunakan lampu LED, dan mengoptimalkan AC, rata-rata rumah tangga dapat menghemat 20-30% dari tagihan listrik.',
                'opsi' => [
                    ['teks' => '5-10%', 'benar' => false],
                    ['teks' => '20-30%', 'benar' => true],
                    ['teks' => '50-60%', 'benar' => false],
                    ['teks' => '70-80%', 'benar' => false],
                ],
            ],
        ];

        $this->seedPertanyaan($kuis3, $pertanyaan3);

        $this->command->info('✅ 3 kuis berhasil ditambahkan (total 12 pertanyaan)');

        // ─────────────────────────────────────────
        // 4. FORUM POSTINGAN + BALASAN
        // ─────────────────────────────────────────

        $post1 = Postingan::create([
            'user_id' => $cristian->id,
            'judul'   => 'Pengalaman Pasang Panel Surya di Rumah',
            'isi'     => "Halo teman-teman! Saya baru saja memasang panel surya rooftop di rumah saya. Total kapasitas 3 kWp dan biaya instalasinya sekitar Rp 40 juta.\n\nSetelah 3 bulan pemakaian, tagihan listrik saya turun sekitar 60%! Sebelumnya rata-rata Rp 800.000/bulan, sekarang cuma Rp 300.000-an.\n\nSiapa yang juga sudah pasang panel surya? Atau ada yang mau tanya-tanya tentang proses instalasi? Saya senang berbagi pengalaman 😊",
        ]);
        $post1->created_at = now()->subDays(5);
        $post1->save();

        $balasan1a = Balasan::create([
            'postingan_id' => $post1->id,
            'user_id'      => $deco->id,
            'isi'          => "Wah menarik sekali pengalamannya, Cristian! Saya juga lagi mempertimbangkan untuk pasang panel surya di rumah. Kalau boleh tahu, berapa lama waktu instalasinya? Dan apakah ada kendala perizinan dari PLN?",
        ]);
        $balasan1a->created_at = now()->subDays(4);
        $balasan1a->save();

        $balasan1b = Balasan::create([
            'postingan_id' => $post1->id,
            'user_id'      => $cristian->id,
            'isi'          => "Instalasinya sekitar 2-3 hari saja, Deco. Untuk perizinan PLN, installer yang saya pakai membantu mengurusnya. Prosesnya kurang lebih 2 minggu sampai meteran exim (ekspor-impor) terpasang. Jadi kelebihan listrik dari panel surya bisa dijual balik ke PLN 👍",
        ]);
        $balasan1b->created_at = now()->subDays(3);
        $balasan1b->save();

        $balasan1c = Balasan::create([
            'postingan_id' => $post1->id,
            'user_id'      => $daffa->id,
            'isi'          => "Terima kasih infonya! Menarik banget investasinya bisa balik dalam beberapa tahun. Untuk mahasiswa yang tinggal di kos, ada rekomendasi panel surya portable yang affordable ga?",
        ]);
        $balasan1c->created_at = now()->subDays(2);
        $balasan1c->save();

        $post2 = Postingan::create([
            'user_id' => $deco->id,
            'judul'   => 'Bagaimana Mengajarkan Energi Terbarukan ke Anak-anak?',
            'isi'     => "Sebagai mahasiswa yang aktif di kegiatan sosial, saya ingin membuat program edukasi tentang energi terbarukan yang menarik untuk anak-anak SD di sekitar kampus.\n\nSaat ini saya sudah punya ide untuk menggunakan video animasi dan kuis interaktif, tapi rasanya kurang engaging. Apakah ada yang punya ide kreatif untuk mengajarkan konsep energi terbarukan ke anak-anak usia 8-12 tahun?\n\nSaya juga sedang merencanakan proyek mini dimana anak-anak membuat model turbin angin sederhana dari bahan daur ulang. Ada yang pernah mencoba?",
        ]);
        $post2->created_at = now()->subDays(4);
        $post2->save();

        $balasan2a = Balasan::create([
            'postingan_id' => $post2->id,
            'user_id'      => $adistya->id,
            'isi'          => "Deco, ide yang bagus! Waktu di SMA dulu saya pernah ikut kegiatan serupa. Kami bawa miniatur panel surya ke kelas. Anak-anak bisa lihat langsung bagaimana cahaya matahari menggerakkan kipas kecil. Mungkin bisa dicoba juga!",
        ]);
        $balasan2a->created_at = now()->subDays(3);
        $balasan2a->save();

        $balasan2b = Balasan::create([
            'postingan_id' => $post2->id,
            'user_id'      => $daffa->id,
            'isi'          => "Kalau untuk proyek turbin angin dari bahan daur ulang, saya punya panduan step-by-step yang pernah saya coba untuk tugas kuliah. Bahannya cuma butuh botol plastik, sedotan, dan motor kecil. Hasilnya cukup keren dan anak-anak pasti suka!",
        ]);
        $balasan2b->created_at = now()->subDays(2);
        $balasan2b->save();

        $post3 = Postingan::create([
            'user_id' => $adistya->id,
            'judul'   => 'Peluang Karir di Bidang Energi Terbarukan',
            'isi'     => "Halo semuanya! Sebagai mahasiswa semester 3, saya mulai memikirkan prospek karir setelah lulus nanti. Saya sangat tertarik dengan energi terbarukan dan ingin mendalami bidang ini.\n\nAda yang bisa kasih saran skill apa saja yang harus dikuasai untuk berkarir di bidang energi bersih? Dan bagaimana prospek kerjanya di Indonesia?\n\nTerima kasih sebelumnya 🙏",
        ]);
        $post3->created_at = now()->subDays(2);
        $post3->save();

        $balasan3a = Balasan::create([
            'postingan_id' => $post3->id,
            'user_id'      => $cristian->id,
            'isi'          => "Halo Adistya! Prospek karir di bidang energi terbarukan sangat cerah. Beberapa skill yang relevan:\n\n1. Pemahaman teknis tentang sistem energi terbarukan\n2. Data analysis untuk monitoring performa pembangkit\n3. Project management\n4. Pengetahuan regulasi energi\n\nDi Indonesia, perusahaan seperti Pertamina NRE, PLN, dan banyak startup energi sedang berkembang pesat. Kebutuhan tenaga ahli di bidang ini diproyeksikan meningkat 25% dalam 5 tahun ke depan.",
        ]);
        $balasan3a->created_at = now()->subDays(1);
        $balasan3a->save();

        $post4 = Postingan::create([
            'user_id' => $daffa->id,
            'judul'   => 'Diskusi: Tantangan Transisi Energi di Indonesia',
            'isi'     => "Sebagai mahasiswa yang belajar tentang SDG, saya ingin membuka diskusi tentang tantangan-tantangan utama yang dihadapi Indonesia dalam transisi menuju energi bersih.\n\nBeberapa poin yang menurut saya penting:\n\n1. Subsidi BBM yang masih besar membuat energi fosil terasa \"murah\"\n2. Ketergantungan pada batubara untuk pembangkit listrik (60% bauran energi)\n3. Infrastruktur grid listrik yang belum merata\n4. Kurangnya SDM terampil di bidang energi terbarukan\n5. Regulasi yang sering berubah-ubah\n\nBagaimana menurut teman-teman? Apa solusi yang paling realistis untuk mengatasi tantangan ini?",
        ]);
        $post4->created_at = now()->subDays(1);
        $post4->save();

        $this->command->info('✅ 4 postingan forum + 6 balasan berhasil ditambahkan');

        // ─────────────────────────────────────────
        // 5. HASIL KUIS (simulasi pengerjaan)
        // ─────────────────────────────────────────

        // Cristian mengerjakan Kuis 1 (skor bagus)
        $kuis1Fresh = Kuis::with('pertanyaan.opsi')->find($kuis1->id);
        $this->seedHasilKuis($cristian, $kuis1Fresh, [true, true, true, false, true]); // 4/5 benar

        // Deco mengerjakan Kuis 1 (skor sempurna)
        $this->seedHasilKuis($deco, $kuis1Fresh, [true, true, true, true, true]); // 5/5 benar

        // Adistya mengerjakan Kuis 1 (skor sedang)
        $this->seedHasilKuis($adistya, $kuis1Fresh, [true, false, true, false, false]); // 2/5 benar

        // Cristian mengerjakan Kuis 2 (skor bagus)
        $kuis2Fresh = Kuis::with('pertanyaan.opsi')->find($kuis2->id);
        $this->seedHasilKuis($cristian, $kuis2Fresh, [true, true, false, true]); // 3/4 benar

        // Deco mengerjakan Kuis 3
        $kuis3Fresh = Kuis::with('pertanyaan.opsi')->find($kuis3->id);
        $this->seedHasilKuis($deco, $kuis3Fresh, [true, true, true]); // 3/3 benar

        // Adistya mengerjakan Kuis 2
        $this->seedHasilKuis($adistya, $kuis2Fresh, [false, true, true, false]); // 2/4 benar

        // Daffa mengerjakan Kuis 1
        $this->seedHasilKuis($daffa, $kuis1Fresh, [true, true, false, true, true]); // 4/5 benar

        // Daffa mengerjakan Kuis 3
        $this->seedHasilKuis($daffa, $kuis3Fresh, [true, false, true]); // 2/3 benar

        $this->command->info('✅ 8 hasil kuis berhasil ditambahkan');
        $this->command->newLine();
        $this->command->info('═══════════════════════════════════════════');
        $this->command->info('  🎉 Seeding selesai! Data lengkap.');
        $this->command->info('═══════════════════════════════════════════');
        $this->command->newLine();
        $this->command->info('  Akun Admin:');
        $this->command->info('    Email    : admin@sdg7learn.com');
        $this->command->info('    Password : admin123');
        $this->command->newLine();
        $this->command->info('  Akun User (semua password: password123):');
        $this->command->info('    cristian@gmail.com  | deco@gmail.com');
        $this->command->info('    daffa@gmail.com     | adistya@gmail.com');
        $this->command->newLine();
    }

    /**
     * Helper: Seed pertanyaan + opsi ke kuis.
     */
    private function seedPertanyaan(Kuis $kuis, array $pertanyaanData): void
    {
        foreach ($pertanyaanData as $index => $data) {
            $pertanyaan = Pertanyaan::create([
                'kuis_id'    => $kuis->id,
                'urutan'     => $index + 1,
                'teks'       => $data['teks'],
                'poin'       => $data['poin'],
                'penjelasan' => $data['penjelasan'] ?? null,
            ]);

            foreach ($data['opsi'] as $opsiData) {
                Opsi::create([
                    'pertanyaan_id' => $pertanyaan->id,
                    'teks'          => $opsiData['teks'],
                    'benar'         => $opsiData['benar'],
                ]);
            }
        }
    }

    /**
     * Helper: Simulasi pengerjaan kuis dan simpan HasilKuis.
     *
     * @param array<bool> $jawaban Array boolean mewakili jawaban benar/salah per pertanyaan
     */
    private function seedHasilKuis(User $user, Kuis $kuis, array $jawaban): void
    {
        $pertanyaanList = $kuis->pertanyaan->sortBy('urutan')->values();
        $skor = 0;
        $jumlahBenar = 0;
        $totalPoin = 0;
        $tinjauan = [];

        foreach ($pertanyaanList as $index => $pertanyaan) {
            $benar = $jawaban[$index] ?? false;
            $opsiBenar = $pertanyaan->opsi->firstWhere('benar', true);
            $opsiSalah = $pertanyaan->opsi->firstWhere('benar', false);
            $opsiDipilih = $benar ? $opsiBenar : $opsiSalah;
            $poinDiperoleh = $benar ? $pertanyaan->poin : 0;

            $skor += $poinDiperoleh;
            $totalPoin += $pertanyaan->poin;
            if ($benar) {
                $jumlahBenar++;
            }

            $tinjauan[] = [
                'pertanyaan_id'   => $pertanyaan->id,
                'urutan'          => $pertanyaan->urutan,
                'teks'            => $pertanyaan->teks,
                'poin'            => $pertanyaan->poin,
                'opsi'            => $pertanyaan->opsi->map(fn ($o) => ['id' => $o->id, 'teks' => $o->teks])->values()->toArray(),
                'opsi_dipilih_id' => $opsiDipilih?->id,
                'opsi_dipilih'    => $opsiDipilih?->teks,
                'opsi_benar_id'   => $opsiBenar?->id,
                'opsi_benar'      => $opsiBenar?->teks,
                'benar'           => $benar,
                'poin_diperoleh'  => $poinDiperoleh,
                'penjelasan'      => $pertanyaan->penjelasan,
            ];
        }

        $persentase = $totalPoin > 0 ? round(($skor / $totalPoin) * 100, 2) : 0;

        HasilKuis::create([
            'user_id'           => $user->id,
            'kuis_id'           => $kuis->id,
            'kuis_judul'        => $kuis->judul,
            'skor'              => $skor,
            'skor_maks'         => $totalPoin,
            'persentase'        => $persentase,
            'jumlah_benar'      => $jumlahBenar,
            'jumlah_pertanyaan' => $pertanyaanList->count(),
            'tinjauan'          => $tinjauan,
        ]);
    }
}
