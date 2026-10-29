<?php
require_once __DIR__ . '/../config/database.php';

function getKriteria($conn) {
    $result = $conn->query("SELECT * FROM kriteria ORDER BY id");
    $kriteria = [];
    while ($row = $result->fetch_assoc()) {
        $kriteria[] = $row;
    }
    return $kriteria;
}

function normalisasiBobot($kriteria) {
    $total = array_sum(array_column($kriteria, 'bobot'));
    if ($total <= 0) {
        $total = 1; 
    }
    foreach ($kriteria as &$k) {
        $k['bobot_normal'] = $k['bobot'] / $total;
    }
    unset($k); 
    return $kriteria;
}

function formatPersentaseRanking($nilaiV, $decimals = 4) {
    return number_format($nilaiV * 100, $decimals, '.', '');
}

function hitungWP($alternatif, $kriteria) {
    // alternatif: array of object dengan nilai_c1..c9
    // kriteria: array dengan bobot_normal dan atribut
    $kriteria = normalisasiBobot($kriteria);
    $m = count($alternatif);
    $n = count($kriteria);
    
    // Hitung vektor S untuk tiap alternatif
    $S = [];
    foreach ($alternatif as $alt) {
        $prod = 1;
        for ($j=0; $j<$n; $j++) {
            $kode = 'nilai_c' . ($j+1);
            $nilai = $alt[$kode];
            if ($nilai <= 0) {
                $nilai = 0.0001; 
            }
            $bobot = $kriteria[$j]['bobot_normal'];
            $atribut = $kriteria[$j]['atribut'];
            // pangkat positif untuk benefit, negatif untuk cost
            $pangkat = ($atribut == 'benefit') ? $bobot : -$bobot;
            $prod *= pow($nilai, $pangkat);
        }
        $S[] = $prod;
    }
    
    // Hitung total S
    $totalS = array_sum($S);
    
    // Hitung vektor V 
    $V = [];
    foreach ($S as $val) {
        $V[] = $val / $totalS;
    }
    
    // Gabungkan dengan alternatif
    $hasil = [];
    for ($i=0; $i<$m; $i++) {
        $alt = $alternatif[$i];
        $alt['nilai_S'] = $S[$i];
        $alt['nilai_V'] = $V[$i];
        $alt['nilai_persen'] = $V[$i] * 100;
        $hasil[] = $alt;
    }
    
    // Urutkan berdasarkan V desc (ranking)
    usort($hasil, function($a, $b) {
        return $b['nilai_V'] <=> $a['nilai_V'];
    });
    
    return $hasil;
}
?>