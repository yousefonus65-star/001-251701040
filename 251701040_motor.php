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

        if ($s['urunler'][$u]['stok'] <= 0) {
            $s['memnuniyet'] = max(0, $s['memnuniyet'] - 5);
            $s['seri'] = 0;
            $s['gunluk_ret']++;
            $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış yok";
        } else {
            $tutar = $s['urunler'][$u]['fiyat'];
            if ($m['tip'] === 'Öğrenci' || $m['tip'] === 'Burslu Öğrenci') {
                $tutar = intdiv($tutar * 90, 100);
            }

            if ($m['butce'] < $tutar) {
                $s['memnuniyet'] = max(0, $s['memnuniyet'] - 2);
                $s['seri'] = 0;
                $s['gunluk_ret']++;
                $s['ret_sayac'][$u]++;

                if ($s['ret_sayac'][$u] >= 2) {
                    $s['urunler'][$u]['fiyat'] -= intdiv($s['urunler'][$u]['fiyat'], 10);
                    $s['ret_sayac'][$u] = 0;
                }

                $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış yok";
            } else {
                $s['urunler'][$u]['stok'] -= 1;
                $s['kasa'] += $tutar;
                $s['memnuniyet'] = min(100, $s['memnuniyet'] + 1);
                $s['seri']++;

                if ($s['seri'] >= 3) {
                    $s['kasa'] += 10;
                    $s['seri'] = 0;
                }

                if ($s['urunler'][$u]['stok'] <= 3 && !$s['zam'][$u]) {
                    $s['urunler'][$u]['fiyat'] += 5;
                    $s['zam'][$u] = true;
                }

                $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış: 1 adet";
            }
        }

        return $s;
    }

    if (preg_match('/^T-([a-z]+)-(\d+)$/', $a, $eslesme) === 1) {
        $u = $eslesme[1];
        $adet = (int)$eslesme[2];

        if (!isset($s['urunler'][$u]) || $adet <= 0) {
            return $s;
        }

        $birim = $s['urunler'][$u]['fiyat'] * 2;
        if ($adet >= 5) {
            $birim = intdiv($birim * 80, 100);
        }

        $alinan = $birim > 0 ? min($adet, max(0, intdiv($s['kasa'], $birim))) : 0;
        $s['urunler'][$u]['stok'] += $alinan;
        $s['kasa'] -= $alinan * $birim;
        $s['log'][] = "🚚 {$s['urunler'][$u]['ad']} tedarik: {$alinan} adet";

        return $s;
    }

    return $s;
}
