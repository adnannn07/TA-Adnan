<?php
/**
 * includes/kriteria_config.php
 *
 * Definisi sub-kriteria PERSIS sesuai Bab IV (Pembahasan Algoritma) skripsi.
 * Dipakai oleh form Tambah/Edit Produk supaya admin memilih tingkatan
 * (Rendah/Sedang/Tinggi = 0.15/0.35/0.50) per sub-kriteria, bukan mengetik
 * angka mentah -- sehingga hasil perhitungan di web selalu konsisten dengan
 * metodologi yang didokumentasikan di PDF.
 */

$KRITERIA_CONFIG = [
    'c1' => [
        'nama' => 'Processor', 'bobot' => 0.20,
        'sub' => [
            ['key'=>'seri', 'label'=>'Seri CPU', 'options'=>[
                ['val'=>0.15,'label'=>'Rendah — Celeron / Pentium / i3'],
                ['val'=>0.35,'label'=>'Sedang — i5 / Ryzen 5'],
                ['val'=>0.50,'label'=>'Tinggi — i7 / i9 / Ryzen 7 / Ryzen 9'],
            ]],
            ['key'=>'generasi', 'label'=>'Generasi CPU', 'options'=>[
                ['val'=>0.15,'label'=>'Rendah — Gen ≤ 9'],
                ['val'=>0.35,'label'=>'Sedang — Gen 10-12'],
                ['val'=>0.50,'label'=>'Tinggi — Gen ≥ 13'],
            ]],
            ['key'=>'core', 'label'=>'Jumlah Core', 'options'=>[
                ['val'=>0.15,'label'=>'Rendah — ≤ 4 core'],
                ['val'=>0.35,'label'=>'Sedang — 6-8 core'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 10 core'],
            ]],
        ],
    ],
    'c2' => [
        'nama' => 'Motherboard', 'bobot' => 0.10,
        'sub' => [
            ['key'=>'chipset','label'=>'Chipset','options'=>[
                ['val'=>0.15,'label'=>'Rendah — H-series'],
                ['val'=>0.35,'label'=>'Sedang — B-series'],
                ['val'=>0.50,'label'=>'Tinggi — Z / X-series'],
            ]],
            ['key'=>'ram_support','label'=>'Support RAM','options'=>[
                ['val'=>0.15,'label'=>'Rendah — DDR3'],
                ['val'=>0.35,'label'=>'Sedang — DDR4'],
                ['val'=>0.50,'label'=>'Tinggi — DDR5'],
            ]],
            ['key'=>'slot_m2','label'=>'Slot M.2 NVMe','options'=>[
                ['val'=>0.15,'label'=>'Rendah — tidak ada slot'],
                ['val'=>0.35,'label'=>'Sedang — 1 slot'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 2 slot'],
            ]],
        ],
    ],
    'c3' => [
        'nama' => 'RAM', 'bobot' => 0.15,
        'sub' => [
            ['key'=>'kapasitas','label'=>'Kapasitas RAM','options'=>[
                ['val'=>0.15,'label'=>'Rendah — ≤ 8GB'],
                ['val'=>0.35,'label'=>'Sedang — 16GB'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 32GB'],
            ]],
            ['key'=>'tipe','label'=>'Tipe RAM','options'=>[
                ['val'=>0.15,'label'=>'Rendah — DDR3'],
                ['val'=>0.35,'label'=>'Sedang — DDR4'],
                ['val'=>0.50,'label'=>'Tinggi — DDR5'],
            ]],
            ['key'=>'channel','label'=>'Konfigurasi Channel','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Single'],
                ['val'=>0.35,'label'=>'Sedang — Dual'],
                ['val'=>0.50,'label'=>'Tinggi — Quad'],
            ]],
        ],
    ],
    'c4' => [
        'nama' => 'Storage', 'bobot' => 0.12,
        'sub' => [
            ['key'=>'jenis','label'=>'Jenis Storage','options'=>[
                ['val'=>0.15,'label'=>'Rendah — HDD'],
                ['val'=>0.35,'label'=>'Sedang — SSD SATA'],
                ['val'=>0.50,'label'=>'Tinggi — NVMe'],
            ]],
            ['key'=>'kapasitas','label'=>'Kapasitas Storage','options'=>[
                ['val'=>0.15,'label'=>'Rendah — ≤ 256GB'],
                ['val'=>0.35,'label'=>'Sedang — 512GB - 1TB'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 2TB'],
            ]],
            ['key'=>'interface','label'=>'Interface/Protocol','options'=>[
                ['val'=>0.15,'label'=>'Rendah — SATA I/II'],
                ['val'=>0.35,'label'=>'Sedang — SATA III'],
                ['val'=>0.50,'label'=>'Tinggi — NVMe'],
            ]],
        ],
    ],
    'c5' => [
        'nama' => 'VGA/GPU', 'bobot' => 0.18,
        'sub' => [
            ['key'=>'seri','label'=>'Seri VGA','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Onboard / GT'],
                ['val'=>0.35,'label'=>'Sedang — GTX / RX 5xx-6xx'],
                ['val'=>0.50,'label'=>'Tinggi — RTX'],
            ]],
            ['key'=>'vram','label'=>'Kapasitas VRAM','options'=>[
                ['val'=>0.15,'label'=>'Rendah — ≤ 4GB'],
                ['val'=>0.35,'label'=>'Sedang — 6-8GB'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 10GB'],
            ]],
            ['key'=>'memtype','label'=>'Tipe Memori GPU','options'=>[
                ['val'=>0.15,'label'=>'Rendah — DDR3 / GDDR5'],
                ['val'=>0.35,'label'=>'Sedang — GDDR6'],
                ['val'=>0.50,'label'=>'Tinggi — GDDR6X / GDDR7'],
            ]],
        ],
    ],
    'c6' => [
        'nama' => 'PSU', 'bobot' => 0.08,
        'sub' => [
            ['key'=>'daya','label'=>'Kapasitas Daya','options'=>[
                ['val'=>0.15,'label'=>'Rendah — ≤ 450W'],
                ['val'=>0.35,'label'=>'Sedang — 500-700W'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 750W'],
            ]],
            ['key'=>'sertifikasi','label'=>'Sertifikasi Efisiensi','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Non 80+'],
                ['val'=>0.35,'label'=>'Sedang — 80+ Certified/Bronze'],
                ['val'=>0.50,'label'=>'Tinggi — 80+ Gold/Platinum'],
            ]],
        ],
    ],
    'c7' => [
        'nama' => 'Cooling', 'bobot' => 0.07,
        'sub' => [
            ['key'=>'jenis','label'=>'Jenis Pendingin','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Stock / Air Cooler'],
                ['val'=>0.35,'label'=>'Sedang — Air Tower'],
                ['val'=>0.50,'label'=>'Tinggi — Liquid AIO'],
            ]],
            ['key'=>'fan','label'=>'Ukuran/Jumlah Fan','options'=>[
                ['val'=>0.15,'label'=>'Rendah — 1 fan'],
                ['val'=>0.35,'label'=>'Sedang — 2-3 fan / AIO 240mm'],
                ['val'=>0.50,'label'=>'Tinggi — ≥ 4 fan / AIO 360mm'],
            ]],
        ],
    ],
    'c8' => [
        'nama' => 'Casing', 'bobot' => 0.05,
        'sub' => [
            ['key'=>'form_factor','label'=>'Form Factor / Ukuran','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Micro ATX'],
                ['val'=>0.35,'label'=>'Sedang — Mid Tower'],
                ['val'=>0.50,'label'=>'Tinggi — Full Tower'],
            ]],
            ['key'=>'airflow','label'=>'Kualitas Airflow','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Solid'],
                ['val'=>0.35,'label'=>'Sedang — Ventilasi'],
                ['val'=>0.50,'label'=>'Tinggi — Mesh'],
            ]],
            ['key'=>'material','label'=>'Material & Build Quality','options'=>[
                ['val'=>0.15,'label'=>'Rendah — Besi'],
                ['val'=>0.35,'label'=>'Sedang — Tempered Glass'],
                ['val'=>0.50,'label'=>'Tinggi — Aluminium / ROG'],
            ]],
        ],
    ],
];

/**
 * C9 Harga Total bukan input sub-kriteria manual, melainkan dihitung otomatis
 * dari total harga (Rupiah) berdasarkan bracket di Bab IV bagian "i. C9 Harga Total":
 *   <= Rp 9.999.999          -> Murah  -> 0.50
 *   Rp 10.000.000-19.999.999 -> Sedang -> 0.35
 *   >= Rp 20.000.000         -> Mahal  -> 0.15 (mengikuti pola tier yang sama)
 */
function hargaToTier($total) {
    if ($total <= 9999999) return 0.50;
    if ($total <= 19999999) return 0.35;
    return 0.15;
}
function hargaTierLabel($total) {
    if ($total <= 9999999) return 'Murah (≤ Rp 9.999.999)';
    if ($total <= 19999999) return 'Sedang (Rp 10.000.000 - Rp 19.999.999)';
    return 'Mahal (≥ Rp 20.000.000)';
}

/**
 * Hitung rata-rata sub-kriteria dengan pembulatan round-half-up (0.425 -> 0.43),
 * sesuai konvensi pembulatan yang dipakai di Bab IV (bukan round-half-even PHP standar
 * pada kasus tertentu -- PHP round() sebenarnya sudah round-half-up secara default,
 * beda dengan Python, jadi fungsi ini sekadar pembungkus agar konsisten & eksplisit).
 */
function rataSubKriteria(array $nilai) {
    return round(array_sum($nilai) / count($nilai), 2);
}

/**
 * Rekonstruksi kombinasi sub-kriteria (0.15/0.35/0.50) yang menghasilkan rata-rata
 * (sudah dibulatkan) sama dengan $target -- dipakai untuk mengisi ulang dropdown
 * pada form Edit berdasarkan nilai_cX yang tersimpan di database. Karena rata-rata
 * bisa dihasilkan dari lebih dari satu kombinasi, fungsi ini mengembalikan kombinasi
 * pertama yang cocok (cukup untuk keperluan tampilan form; hasil akhir tetap konsisten
 * karena rata-ratanya identik).
 */
function reconstructSubValues($jumlahSub, $target) {
    $opsi = [0.15, 0.35, 0.50];
    $terbaik = array_fill(0, $jumlahSub, 0.15);
    $selisihTerkecil = 999;
    $kombinasi = [[]];
    for ($i = 0; $i < $jumlahSub; $i++) {
        $baru = [];
        foreach ($kombinasi as $k) {
            foreach ($opsi as $o) {
                $baru[] = array_merge($k, [$o]);
            }
        }
        $kombinasi = $baru;
    }
    foreach ($kombinasi as $k) {
        $rata = rataSubKriteria($k);
        $selisih = abs($rata - $target);
        if ($selisih < $selisihTerkecil) {
            $selisihTerkecil = $selisih;
            $terbaik = $k;
            if ($selisih < 0.001) break;
        }
    }
    return $terbaik;
}
