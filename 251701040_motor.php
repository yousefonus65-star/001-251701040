<?php
// Öğrenci No: 251701040 (Oyun No: 701040)
declare(strict_types=1);

function adim(array $s, string $a): array {
    if ($s['bitti']) {
        return $s;
    }

    if ($a === 'M') {
        if ($s['gunluk_musteri'] >= 5) {
            return $s;
        }

        $s['musteri_sira']++;
        $s['gunluk_musteri']++;

        $m = musteri((int)$s['no'], (int)$s['musteri_sira']);
        $u = $m['urun'];
        $urunAd = $s['urunler'][$u]['ad'];

        if ($s['urunler'][$u]['stok'] > 0) {
            $tutar = $s['urunler'][$u]['fiyat'];
            if ($m['butce'] >= $tutar) {
                $s['urunler'][$u]['stok'] -= 1;
                $s['kasa'] += $tutar;
                $s['memnuniyet'] = min(100, $s['memnuniyet'] + 1);
                $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış: 1 adet";
            }
        }

        return $s;
    }

    return $s;
}
