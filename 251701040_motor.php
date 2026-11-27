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

        $istenen = $s['yogunluk'] ? 2 : 1;
        $s['yogunluk'] = false;

        if ($s['elektrik'] > 0) {
            $s['elektrik']--;
            $s['memnuniyet'] = max(0, $s['memnuniyet'] - 8);
            $s['seri'] = 0;
            $s['gunluk_ret']++;
            $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış yok";
        } elseif ($s['urunler'][$u]['stok'] <= 0) {
            $s['memnuniyet'] = max(0, $s['memnuniyet'] - 5);
            $s['seri'] = 0;
            $s['gunluk_ret']++;
            $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış yok";
        } else {
            $adet = min($istenen, $s['urunler'][$u]['stok']);
            $tutar = $s['urunler'][$u]['fiyat'] * $adet;

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
                $s['urunler'][$u]['stok'] -= $adet;
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

                $s['log'][] = "👤 {$m['ad']} ({$m['tip']}, bütçe {$m['butce']} TL) {$urunAd} istiyor → Satış: {$adet} adet";
            }
        }

        $ev = olay((int)$s['no'], (int)$s['musteri_sira']);
        if ($ev === 'elektrik') {
            $s['elektrik'] = 1;
            $s['log'][] = '⚡ Elektrik kesintisi';
        } elseif ($ev === 'yogunluk') {
            $s['yogunluk'] = true;
            $s['log'][] = '👥 Öğle yoğunluğu';
        } elseif ($ev === 'tedarik') {
            $enCokUrun = null;
            $enCokStok = -1;
            foreach ($s['urunler'] as $anahtar => $bilgi) {
                if ($bilgi['stok'] > $enCokStok) {
                    $enCokStok = $bilgi['stok'];
                    $enCokUrun = $anahtar;
                }
            }
            if ($enCokUrun !== null) {
                $s['urunler'][$enCokUrun]['stok'] = max(0, $s['urunler'][$enCokUrun]['stok'] - 2);
            }
            $s['log'][] = '📦 Tedarik sorunu';
        } elseif ($ev === 'bahsis') {
            $s['kasa'] += ($s['memnuniyet'] >= 80) ? 20 : 5;
            $s['log'][] = '😊 Memnun müşteri';
        }

        if ($s['memnuniyet'] <= 0) {
            $s['bitti'] = true;
            $s['kazandi'] = false;
        }

        return $s;
    }

    if ($a === 'G') {
        if ($s['gunluk_musteri'] < 5) {
            return $s;
        }

        $kapananGun = $s['gun'];
        $s['kasa'] -= 50 + 10 * $s['gun'];

        if ($s['gunluk_ret'] === 0) {
            $s['memnuniyet'] = min(100, $s['memnuniyet'] + 3);
        } elseif ($s['gunluk_ret'] >= 2) {
            $s['memnuniyet'] = max(0, $s['memnuniyet'] - 5);
        }

        $s['urunler']['tost']['stok'] -= intdiv($s['urunler']['tost']['stok'], 2);
        $s['urunler']['hamburger']['stok'] -= intdiv($s['urunler']['hamburger']['stok'], 2);

        $s['gunluk_musteri'] = 0;
        $s['gunluk_ret'] = 0;
        $s['seri'] = 0;
        $s['elektrik'] = 0;
        $s['yogunluk'] = false;
        $s['log'][] = "🌙 Gün {$kapananGun} kapandı";

        if ($s['kasa'] < 0 || $s['memnuniyet'] <= 0) {
            $s['bitti'] = true;
            $s['kazandi'] = false;
        } elseif ($s['gun'] >= 5) {
            $s['bitti'] = true;
            $s['kazandi'] = ($s['kasa'] >= 1000 && $s['memnuniyet'] >= 60);
        } else {
            $s['gun']++;
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
