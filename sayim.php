<?php
// ─── EXCEL EXPORT: header.php'den ÖNCE çalışmalı ───
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='excel_export'){
    session_start();
    include "db.php";

    $depo_adi_exp  = $_POST['depo_adi'] ?? 'Depo';
    $sayim_no_exp  = $_POST['sayim_no'] ?? '';
    $tarih_exp     = $_POST['sayim_tarihi'] ?? date('d.m.Y H:i');
    $kullanici_exp = $_SESSION['username'] ?? '-';
    $satirlar      = json_decode($_POST['satirlar'], true) ?? [];

    $xlsx_dosya = sys_get_temp_dir() . '/sayim_' . uniqid() . '.xlsx';
    olustur_sayim_xlsx($xlsx_dosya, $satirlar, $depo_adi_exp, $sayim_no_exp, $tarih_exp, $kullanici_exp);

    $dosya_adi = 'sayim_' . preg_replace('/[^a-zA-Z0-9]/','_', $depo_adi_exp) . '_' . date('Ymd_Hi') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $dosya_adi . '"');
    header('Content-Transfer-Encoding: binary');
    header('Cache-Control: must-revalidate');
    header('Content-Length: ' . filesize($xlsx_dosya));
    ob_end_clean();
    readfile($xlsx_dosya);
    unlink($xlsx_dosya);
    exit();
}

// XLSX OLUŞTURMA FONKSİYONU — burada tanımla ki export bloğu kullanabilsin
function olustur_sayim_xlsx($dosya_yolu, $satirlar, $depo_adi, $sayim_no, $tarih, $kullanici){
    $toplam  = count($satirlar);
    $eslesme = count(array_filter($satirlar, fn($r)=>intval($r['fark'])==0));
    $eksik   = count(array_filter($satirlar, fn($r)=>intval($r['fark'])<0));
    $fazla   = count(array_filter($satirlar, fn($r)=>intval($r['fark'])>0));

    $ss = []; $ss_idx = 0;
    $get_ss = function($val) use (&$ss, &$ss_idx) {
        $val = (string)$val;
        if(!isset($ss[$val])){ $ss[$val] = $ss_idx++; }
        return $ss[$val];
    };

    $veri_bas = 10;
    $cols = ['A','B','C','D','E','F'];

    $sx = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
        . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheetFormatPr defaultRowHeight="18"/>'
        . '<cols>'
        . '<col min="1" max="1" width="36" customWidth="1"/>'
        . '<col min="2" max="2" width="20" customWidth="1"/>'
        . '<col min="3" max="3" width="16" customWidth="1"/>'
        . '<col min="4" max="4" width="16" customWidth="1"/>'
        . '<col min="5" max="5" width="14" customWidth="1"/>'
        . '<col min="6" max="6" width="16" customWidth="1"/>'
        . '</cols><sheetData>';

    $sx .= '<row r="1" ht="8" customHeight="1"><c r="A1" s="0"/></row>';

    // Başlık satırı
    $sx .= '<row r="2" ht="40" customHeight="1">'
        . '<c r="A2" t="s" s="1"><v>' . $get_ss('DEPO SAYIM RAPORU') . '</v></c>'
        . '<c r="B2" s="1"/><c r="C2" s="1"/><c r="D2" s="1"/><c r="E2" s="1"/><c r="F2" s="1"/>'
        . '</row>';

    $sx .= '<row r="3" ht="6" customHeight="1"><c r="A3" s="0"/></row>';

    // Bilgi satırları
    foreach([['Sayim No', $sayim_no], ['Depo', $depo_adi], ['Tarih', $tarih], ['Sayan', $kullanici]] as $i => $b){
        $r = 4 + $i;
        $sx .= '<row r="' . $r . '" ht="20" customHeight="1">'
            . '<c r="A' . $r . '" t="s" s="2"><v>' . $get_ss($b[0]) . '</v></c>'
            . '<c r="B' . $r . '" t="s" s="3"><v>' . $get_ss($b[1]) . '</v></c>'
            . '<c r="C' . $r . '" s="3"/><c r="D' . $r . '" s="3"/>'
            . '<c r="E' . $r . '" s="3"/><c r="F' . $r . '" s="3"/>'
            . '</row>';
    }

    $sx .= '<row r="8" ht="8" customHeight="1"><c r="A8" s="0"/></row>';

    // Tablo başlıkları
    foreach(['Urun Adi','Barkod','Sistem Stoku','Sayilan Miktar','Fark','Durum'] as $j => $b){
        $baslik_row = '<c r="' . $cols[$j] . '9" t="s" s="4"><v>' . $get_ss($b) . '</v></c>';
        $sx = ($j==0) ? $sx . '<row r="9" ht="24" customHeight="1">' . $baslik_row
                      : $sx . $baslik_row;
    }
    $sx .= '</row>';

    // Veri satırları
    foreach($satirlar as $k => $row){
        $r    = $veri_bas + $k;
        $alt  = ($k % 2 == 1);
        $fark = intval($row['fark']);

        if($fark == 0){
            $fark_val = '-'; $durum = 'Eslesiyor';
            $fark_s = 10; $durum_s = 12;
        } elseif($fark > 0){
            $fark_val = '+' . $fark; $durum = 'Fazla';
            $fark_s = 14; $durum_s = 14;
        } else {
            $fark_val = (string)$fark; $durum = 'Eksik';
            $fark_s = 16; $durum_s = 16;
        }

        $sx .= '<row r="' . $r . '" ht="22" customHeight="1">'
            . '<c r="A' . $r . '" t="s" s="' . ($alt?6:5) . '"><v>' . $get_ss((string)$row['urun_adi']) . '</v></c>'
            . '<c r="B' . $r . '" t="s" s="' . ($alt?8:7) . '"><v>' . $get_ss((string)$row['barkod']) . '</v></c>'
            . '<c r="C' . $r . '" s="9"><v>' . intval($row['sistem_stok']) . '</v></c>'
            . '<c r="D' . $r . '" s="9"><v>' . intval($row['sayilan']) . '</v></c>'
            . '<c r="E' . $r . '" t="s" s="' . $fark_s . '"><v>' . $get_ss($fark_val) . '</v></c>'
            . '<c r="F' . $r . '" t="s" s="' . $durum_s . '"><v>' . $get_ss($durum) . '</v></c>'
            . '</row>';
    }

    // Özet
    $ozet1 = $veri_bas + count($satirlar) + 2;
    $ozet2 = $ozet1 + 1;

    $sx .= '<row r="' . $ozet1 . '" ht="20" customHeight="1">'
        . '<c r="A' . $ozet1 . '" t="s" s="18"><v>' . $get_ss('TOPLAM URUN') . '</v></c>'
        . '<c r="B' . $ozet1 . '" t="s" s="18"><v>' . $get_ss('ESLESIYOR') . '</v></c>'
        . '<c r="C' . $ozet1 . '" t="s" s="18"><v>' . $get_ss('EKSIK') . '</v></c>'
        . '<c r="D' . $ozet1 . '" t="s" s="18"><v>' . $get_ss('FAZLA') . '</v></c>'
        . '</row>';

    $sx .= '<row r="' . $ozet2 . '" ht="28" customHeight="1">'
        . '<c r="A' . $ozet2 . '" s="19"><v>' . $toplam . '</v></c>'
        . '<c r="B' . $ozet2 . '" s="20"><v>' . $eslesme . '</v></c>'
        . '<c r="C' . $ozet2 . '" s="21"><v>' . $eksik . '</v></c>'
        . '<c r="D' . $ozet2 . '" s="22"><v>' . $fazla . '</v></c>'
        . '</row>';

    $footer = $ozet2 + 2;
    $sx .= '<row r="' . $footer . '" ht="16" customHeight="1">'
        . '<c r="A' . $footer . '" t="s" s="23"><v>' . $get_ss('Stok Takip Sistemi (c) 2026') . '</v></c>'
        . '</row>';

    $sx .= '</sheetData>';
    $sx .= '<mergeCells count="6">'
        . '<mergeCell ref="A2:F2"/>'
        . '<mergeCell ref="B4:F4"/>'
        . '<mergeCell ref="B5:F5"/>'
        . '<mergeCell ref="B6:F6"/>'
        . '<mergeCell ref="B7:F7"/>'
        . '<mergeCell ref="A' . $footer . ':F' . $footer . '"/>'
        . '</mergeCells>';
    $sx .= '</worksheet>';

    // Shared strings XML
    $ss_sorted = array_flip($ss); ksort($ss_sorted);
    $ss_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($ss_sorted) . '" uniqueCount="' . count($ss_sorted) . '">';
    foreach($ss_sorted as $val){
        $ss_xml .= '<si><t xml:space="preserve">' . htmlspecialchars((string)$val, ENT_XML1, 'UTF-8') . '</t></si>';
    }
    $ss_xml .= '</sst>';

    $styles_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="13">
    <font><sz val="11"/><name val="Arial"/></font>
    <font><b/><sz val="16"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><b/><sz val="10"/><color rgb="FF7A8399"/><name val="Arial"/></font>
    <font><b/><sz val="10"/><color rgb="FF111111"/><name val="Arial"/></font>
    <font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="FF111111"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="FF111111"/><name val="Arial"/></font>
    <font><sz val="9"/><color rgb="FF7A8399"/><name val="Courier New"/></font>
    <font><sz val="9"/><color rgb="FF7A8399"/><name val="Courier New"/></font>
    <font><b/><sz val="11"/><color rgb="FF111111"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="FF22C55E"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="FFFACC15"/><name val="Arial"/></font>
    <font><b/><sz val="11"/><color rgb="FFF43F5E"/><name val="Arial"/></font>
  </fonts>
  <fills count="16">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF97316"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF0F2F5"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF1C2333"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF9FAFB"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFFFFF"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF9FAFB"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEF9C3"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFF1F2"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF0F2F5"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFEF9C3"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFFFF1F2"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/></border>
    <border>
      <left style="thin"><color rgb="FFDDDDDD"/></left>
      <right style="thin"><color rgb="FFDDDDDD"/></right>
      <top style="thin"><color rgb="FFDDDDDD"/></top>
      <bottom style="thin"><color rgb="FFDDDDDD"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="24">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="3" fillId="3" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="5" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="6" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="7" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="8" fillId="6" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="left" vertical="center" indent="1"/></xf>
    <xf numFmtId="0" fontId="9" fillId="5" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="10" fillId="9" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="10" fillId="13" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="10" fillId="9" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="10" fillId="13" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="11" fillId="10" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="11" fillId="14" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="12" fillId="11" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="12" fillId="15" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="5" fillId="12" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="10" fillId="13" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="12" fillId="11" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="11" fillId="10" borderId="1" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>
  </cellXfs>
</styleSheet>';

    $wb_xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
        . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets><sheet name="Sayim Raporu" sheetId="1" r:id="rId1"/></sheets>'
        . '</workbook>';

    $wb_rel = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
        . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
        . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>';

    $content_types = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
        . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>';

    $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>';

    $zip = new ZipArchive();
    if($zip->open($dosya_yolu, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true){
        return false;
    }
    $zip->addFromString('[Content_Types].xml', $content_types);
    $zip->addFromString('_rels/.rels', $rels);
    $zip->addFromString('xl/workbook.xml', $wb_xml);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $wb_rel);
    $zip->addFromString('xl/worksheets/sheet1.xml', $sx);
    $zip->addFromString('xl/sharedStrings.xml', $ss_xml);
    $zip->addFromString('xl/styles.xml', $styles_xml);
    $zip->close();
    return true;
}

// ─── Normal sayfa akışı ───
include "header.php";
include "db.php";

$sayim_yapildi = false;
$mesaj = null;
$mesaj_tip = null;
$sayim_sonuclari = [];
$secili_depo = null;

// Depolar listesi
$depolar_sonuc = $baglanti->query("SELECT * FROM depolar ORDER BY depo_adi");
$depolar = [];
while($d = $depolar_sonuc->fetch_assoc()) $depolar[] = $d;

// Sayım formu gönderildi mi?
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['action'])){

  // Adım 1: Depo seçildi → sayım formu göster
  if($_POST['action']==='secim'){
    $secili_depo_id = intval($_POST['depo_id']);
    foreach($depolar as $d){
      if($d['id']==$secili_depo_id){ $secili_depo=$d; break; }
    }
  }

  // Adım 2: Sayım kaydedildi
  if($_POST['action']==='kaydet'){
    $secili_depo_id = intval($_POST['depo_id']);
    foreach($depolar as $d){
      if($d['id']==$secili_depo_id){ $secili_depo=$d; break; }
    }

    $stok_q = $baglanti->query("
      SELECT u.id, u.urun_adi, u.barkod, GREATEST(0, COALESCE(s.miktar,0)) AS sistem_stok
      FROM urunler u
      LEFT JOIN stok s ON s.urun_id=u.id AND s.depo_id='$secili_depo_id'
      ORDER BY u.urun_adi
    ");

    while($row = $stok_q->fetch_assoc()){
      $uid = $row['id'];
      $sayilan = isset($_POST['sayilan'][$uid]) ? intval($_POST['sayilan'][$uid]) : 0;
      $sistem  = intval($row['sistem_stok']);
      $fark    = $sayilan - $sistem;
      $sayim_sonuclari[] = [
        'id'         => $uid,
        'urun_adi'   => $row['urun_adi'],
        'barkod'     => $row['barkod'],
        'sistem_stok'=> $sistem,
        'sayilan'    => $sayilan,
        'fark'       => $fark,
      ];
    }

    $sayim_yapildi = true;
    $sayim_tarihi  = date('d.m.Y H:i');
    $sayim_kullanici = $_SESSION['username'] ?? '-';
    $sayim_no = 'SAY-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()),0,5));
  }

}
?>

<div style="margin-bottom:28px">
  <div class="page-heading"><?= $T['count_h1'] ?> <span><?= $T['count_h2'] ?></span></div>
  <div class="page-subheading"><?= $T['count_sub'] ?></div>
</div>

<?php if($sayim_yapildi && $secili_depo): ?>
<!-- ============================================================ -->
<!-- SAYIM RAPORU                                                  -->
<!-- ============================================================ -->
<?php
$toplam = count($sayim_sonuclari);
$eslesme = count(array_filter($sayim_sonuclari, fn($r)=>$r['fark']===0));
$eksik   = count(array_filter($sayim_sonuclari, fn($r)=>$r['fark']<0));
$fazla   = count(array_filter($sayim_sonuclari, fn($r)=>$r['fark']>0));
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div style="display:flex;align-items:center;gap:10px">
    <div style="width:32px;height:32px;background:rgba(34,197,94,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center">
      <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:16px"></i>
    </div>
    <span style="font-size:17px;font-weight:700;color:var(--success)"><?= $T['count_success'] ?></span>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap">
    <!-- Excel Export -->
    <form method="post" style="display:inline">
      <input type="hidden" name="action" value="excel_export">
      <input type="hidden" name="depo_adi" value="<?= htmlspecialchars($secili_depo['depo_adi']) ?>">
      <input type="hidden" name="sayim_no" value="<?= htmlspecialchars($sayim_no ?? '') ?>">
      <input type="hidden" name="sayim_tarihi" value="<?= htmlspecialchars($sayim_tarihi ?? '') ?>">
      <input type="hidden" name="satirlar" value="<?= htmlspecialchars(json_encode($sayim_sonuclari)) ?>">
      <button type="submit" class="btn-accent" style="background:#22c55e;box-shadow:0 4px 15px rgba(34,197,94,0.3)">
        <i class="bi bi-file-earmark-excel"></i> Excel'e Aktar
      </button>
    </form>
    <button onclick="yazdirSayim()" class="btn-accent">
      <i class="bi bi-printer"></i> <?= $T['btn_print_count'] ?>
    </button>
    <a href="sayim.php" class="btn-accent" style="background:var(--bg-card);color:var(--text-muted);border:1px solid var(--border);box-shadow:none">
      <?= $T['btn_new_count'] ?>
    </a>
  </div>
</div>

<!-- Özet kartlar -->
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3">
    <div class="card" style="padding:16px;text-align:center">
      <div style="font-size:28px;font-weight:700;color:var(--text-primary);font-family:'Space Mono',monospace"><?= $toplam ?></div>
      <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px">Toplam Ürün</div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card" style="padding:16px;text-align:center;border-color:rgba(34,197,94,0.25)">
      <div style="font-size:28px;font-weight:700;color:var(--success);font-family:'Space Mono',monospace"><?= $eslesme ?></div>
      <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px"><?= $T['count_match'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card" style="padding:16px;text-align:center;border-color:rgba(244,63,94,0.25)">
      <div style="font-size:28px;font-weight:700;color:var(--danger);font-family:'Space Mono',monospace"><?= $eksik ?></div>
      <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px"><?= $T['count_missing'] ?></div>
    </div>
  </div>
  <div class="col-6 col-md-3">
    <div class="card" style="padding:16px;text-align:center;border-color:rgba(250,204,21,0.25)">
      <div style="font-size:28px;font-weight:700;color:var(--warning);font-family:'Space Mono',monospace"><?= $fazla ?></div>
      <div style="font-size:11px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-top:4px"><?= $T['count_excess'] ?></div>
    </div>
  </div>
</div>

<!-- Rapor tablosu -->
<div id="sayim-raporu">
  <div class="rapor-baslik" style="display:none;margin-bottom:24px">
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
      <div style="width:40px;height:40px;background:#f97316;border-radius:10px;display:flex;align-items:center;justify-content:center">
        <i class="bi bi-clipboard2-check" style="color:#fff;font-size:18px"></i>
      </div>
      <div>
        <div style="font-size:18px;font-weight:700;color:#111;font-family:'Space Mono',monospace"><?= $T['count_report_title'] ?></div>
        <div style="font-size:12px;color:#666;font-family:'Space Mono',monospace"><?= htmlspecialchars($sayim_no ?? '') ?></div>
      </div>
    </div>
    <div style="display:flex;gap:24px;font-size:13px;color:#555;font-family:'Space Mono',monospace;flex-wrap:wrap">
      <span><i class="bi bi-building"></i> <?= $T['count_depot'] ?>: <?= htmlspecialchars($secili_depo['depo_adi']) ?></span>
      <span><i class="bi bi-calendar3"></i> <?= $T['count_date'] ?>: <?= htmlspecialchars($sayim_tarihi ?? '') ?></span>
      <span><i class="bi bi-person"></i> <?= $T['count_user'] ?>: <?= htmlspecialchars($sayim_kullanici) ?></span>
    </div>
  </div>

  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th><?= $T['col_count_product'] ?></th>
          <th style="text-align:right"><?= $T['col_system_stock'] ?></th>
          <th style="text-align:right"><?= $T['col_actual_stock'] ?></th>
          <th style="text-align:center"><?= $T['col_difference'] ?></th>
          <th style="text-align:center">Durum</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($sayim_sonuclari as $s): ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:14px;color:var(--text-primary)"><?= htmlspecialchars($s['urun_adi']) ?></div>
            <div style="font-size:11px;color:var(--text-muted);font-family:'Space Mono',monospace"><?= htmlspecialchars($s['barkod']) ?></div>
          </td>
          <td style="text-align:right;font-family:'Space Mono',monospace;font-weight:600;color:var(--text-primary)"><?= $s['sistem_stok'] ?></td>
          <td style="text-align:right;font-family:'Space Mono',monospace;font-weight:600;color:var(--text-primary)"><?= $s['sayilan'] ?></td>
          <td style="text-align:center">
            <?php if($s['fark']==0): ?>
              <span style="font-family:'Space Mono',monospace;color:var(--text-muted)">—</span>
            <?php elseif($s['fark']>0): ?>
              <span style="font-family:'Space Mono',monospace;font-weight:700;color:var(--warning)">+<?= $s['fark'] ?></span>
            <?php else: ?>
              <span style="font-family:'Space Mono',monospace;font-weight:700;color:var(--danger)"><?= $s['fark'] ?></span>
            <?php endif; ?>
          </td>
          <td style="text-align:center">
            <?php if($s['fark']==0): ?>
              <span class="badge-giris"><i class="bi bi-check-lg"></i> <?= $T['count_match'] ?></span>
            <?php elseif($s['fark']>0): ?>
              <span style="background:rgba(250,204,21,0.15);color:var(--warning);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;font-family:'Space Mono',monospace"><i class="bi bi-arrow-up"></i> <?= $T['count_excess'] ?></span>
            <?php else: ?>
              <span style="background:rgba(244,63,94,0.15);color:var(--danger);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;font-family:'Space Mono',monospace"><i class="bi bi-arrow-down"></i> <?= $T['count_missing'] ?></span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<style>
@media print {
  .sidebar,.main-wrapper>.top-bar,.page-heading,.page-subheading,
  div[style*="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px"],
  .row.g-3.mb-4{display:none!important;}
  .main-wrapper{margin-left:0!important;}
  .page-content{padding:0!important;}
  #sayim-raporu .rapor-baslik{display:block!important;}
  body{background:#fff!important;color:#111!important;}
  .table-wrapper{border:1px solid #ddd!important;}
  .table thead th{background:#f5f5f5!important;color:#555!important;}
  .table tbody td{color:#111!important;border-color:#eee!important;}
}
</style>

<script>
function yazdirSayim(){
  document.querySelector('.rapor-baslik').style.display='block';
  window.print();
  setTimeout(()=>{ document.querySelector('.rapor-baslik').style.display='none'; },500);
}
</script>

<?php elseif(isset($_POST['action']) && $_POST['action']==='secim' && $secili_depo): ?>
<!-- ============================================================ -->
<!-- SAYIM FORMU                                                   -->
<!-- ============================================================ -->
<?php
$stok_q = $baglanti->query("
  SELECT u.id, u.urun_adi, u.barkod, GREATEST(0, COALESCE(s.miktar,0)) AS sistem_stok
  FROM urunler u
  LEFT JOIN stok s ON s.urun_id=u.id AND s.depo_id='{$secili_depo['id']}'
  ORDER BY u.urun_adi
");
$stok_rows = [];
while($r = $stok_q->fetch_assoc()) $stok_rows[] = $r;
?>

<div style="display:flex;align-items:center;gap:10px;margin-bottom:24px;padding:14px 20px;background:var(--accent-dim);border:1px solid rgba(249,115,22,0.25);border-radius:10px">
  <i class="bi bi-building" style="font-size:18px;color:var(--accent)"></i>
  <div>
    <div style="font-weight:700;color:var(--text-primary)"><?= htmlspecialchars($secili_depo['depo_adi']) ?></div>
    <div style="font-size:12px;color:var(--text-muted)"><?= count($stok_rows) ?> ürün sayılacak</div>
  </div>
</div>

<form method="post">
  <input type="hidden" name="action" value="kaydet">
  <input type="hidden" name="depo_id" value="<?= $secili_depo['id'] ?>">

  <div class="table-wrapper" style="margin-bottom:24px">
    <table class="table">
      <thead>
        <tr>
          <th><?= $T['col_count_product'] ?></th>
          <th style="text-align:right"><?= $T['col_system_stock'] ?></th>
          <th style="text-align:center;min-width:160px"><?= $T['col_actual_stock'] ?></th>
        </tr>
      </thead>
      <tbody>
        <?php
        $is_light = false; // JS ile kontrol edemeyiz, PHP'de localStorage yok
        // Renkleri inline ver — her temada çalışır
        ?>
        <?php foreach($stok_rows as $row): ?>
        <tr>
          <td>
            <div style="font-weight:600;font-size:14px;color:var(--text-primary)"><?= htmlspecialchars($row['urun_adi']) ?></div>
            <div style="font-size:11px;color:var(--text-muted);font-family:'Space Mono',monospace"><?= htmlspecialchars($row['barkod']) ?></div>
          </td>
          <td style="text-align:right;font-family:'Space Mono',monospace;font-weight:700;font-size:16px;color:var(--text-primary)"><?= htmlspecialchars($row['sistem_stok']) ?></td>
          <td style="text-align:center">
            <input
              type="number"
              name="sayilan[<?= $row['id'] ?>]"
              value="<?= htmlspecialchars($row['sistem_stok']) ?>"
              min="0"
              class="form-control sayim-input"
              style="width:110px;margin:0 auto;text-align:center;font-family:'Space Mono',monospace;font-size:15px;font-weight:700"
              data-sistem="<?= $row['sistem_stok'] ?>"
              oninput="farkGoster(this)"
            >
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
    <button type="submit" class="btn-accent">
      <i class="bi bi-clipboard2-check"></i> <?= $T['btn_save_count'] ?>
    </button>
    <a href="sayim.php" style="font-size:13px;color:var(--text-muted);text-decoration:none"><?= $T['btn_new_count'] ?></a>
  </div>
</form>

<script>
function farkGoster(input){
  const sistem = parseInt(input.dataset.sistem)||0;
  const sayilan = parseInt(input.value)||0;
  const fark = sayilan - sistem;
  if(fark>0){
    input.style.borderColor='var(--warning)';
    input.style.color='var(--warning)';
  } else if(fark<0){
    input.style.borderColor='var(--danger)';
    input.style.color='var(--danger)';
  } else {
    input.style.borderColor='var(--border)';
    input.style.color='var(--text-primary)';
  }
}
</script>

<?php else: ?>
<!-- ============================================================ -->
<!-- DEPO SEÇİM EKRANI                                            -->
<!-- ============================================================ -->

<form method="post">
  <input type="hidden" name="action" value="secim">
  <div class="card" style="max-width:480px;padding:32px">
    <div style="margin-bottom:24px">
      <div style="width:48px;height:48px;background:rgba(149,115,255,0.12);border-radius:12px;display:flex;align-items:center;justify-content:center;margin-bottom:14px">
        <i class="bi bi-clipboard2-check" style="font-size:22px;color:#9573ff"></i>
      </div>
      <div style="font-size:15px;font-weight:600;color:var(--text-primary);margin-bottom:4px">Hangi depoyu sayacaksınız?</div>
      <div style="font-size:13px;color:var(--text-muted)">Sayım yapmak istediğiniz depoyu seçin. Sistem stoğu ile karşılaştırma yapılacak.</div>
    </div>

    <div style="margin-bottom:24px">
      <label class="form-label"><?= $T['lbl_count_depot'] ?></label>
      <select class="form-control form-select" name="depo_id" required>
        <option value="">— Depo seçin —</option>
        <?php foreach($depolar as $d): ?>
        <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['depo_adi']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <button type="submit" class="btn-accent" style="background:#9573ff;box-shadow:0 4px 15px rgba(149,115,255,0.3)">
      <i class="bi bi-arrow-right-circle"></i> <?= $T['btn_count_start'] ?>
    </button>
  </div>
</form>

<?php endif; ?>

<style>
/* Sayım sayfası — tablo yazı rengi kesin fix */
.table tbody tr td,
.table tbody tr td div,
.table tbody tr td span.sis-stok {
  color: var(--text-primary) !important;
}
.table tbody tr td .text-muted,
.table tbody tr td div[style*="text-muted"] {
  color: var(--text-muted) !important;
}
</style>

<?php include "footer.php"; ?>
