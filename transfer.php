<?php
include "header.php";
include "db.php";

$transfer_yapildi = false;
$mesaj = null; $mesaj_tip = null;
$etiket = [];

if($_POST){
    $urun   = $_POST['urun'];
    $kaynak = $_POST['kaynak'];
    $hedef  = $_POST['hedef'];
    $miktar = $_POST['miktar'];

    if($kaynak === $hedef){
        $mesaj = $T['err_same_depot'];
        $mesaj_tip = 'danger';
    } else {
        $baglanti->query("UPDATE stok SET miktar=miktar-$miktar WHERE urun_id='$urun' AND depo_id='$kaynak'");
        $baglanti->query("UPDATE stok SET miktar=miktar+$miktar WHERE urun_id='$urun' AND depo_id='$hedef'");
        $baglanti->query("INSERT INTO hareketler(urun_id,depo_id,islem,miktar) VALUES('$urun','$kaynak','transfer','$miktar')");

        // Etiket için bilgileri çek
        $urun_row   = $baglanti->query("SELECT * FROM urunler WHERE id='$urun'")->fetch_assoc();
        $kaynak_row = $baglanti->query("SELECT * FROM depolar WHERE id='$kaynak'")->fetch_assoc();
        $hedef_row  = $baglanti->query("SELECT * FROM depolar WHERE id='$hedef'")->fetch_assoc();

        $etiket = [
            'urun_adi'   => $urun_row['urun_adi'] ?? '-',
            'barkod'     => $urun_row['barkod'] ?? '-',
            'kaynak'     => $kaynak_row['depo_adi'] ?? '-',
            'hedef'      => $hedef_row['depo_adi'] ?? '-',
            'miktar'     => $miktar,
            'tarih'      => date('d.m.Y H:i'),
            'kullanici'  => $_SESSION['username'] ?? '-',
            'trf_no'     => 'TRF-' . strtoupper(substr(md5(uniqid()), 0, 8)),
        ];
        $transfer_yapildi = true;
    }
}

// Depoları çek
$depolar_sonuc = $baglanti->query("SELECT * FROM depolar");
$depolar = [];
while($d = $depolar_sonuc->fetch_assoc()) $depolar[] = $d;
?>

<div style="margin-bottom:28px">
  <div class="page-heading"><?= $T['transfer_h1'] ?> <span><?= $T['transfer_h2'] ?></span></div>
  <div class="page-subheading"><?= $T['transfer_sub'] ?></div>
</div>

<?php if($mesaj): ?>
<div class="alert alert-<?= $mesaj_tip ?>" style="margin-bottom:24px">
  <i class="bi bi-exclamation-circle"></i> <?= $mesaj ?>
</div>
<?php endif; ?>

<?php if($transfer_yapildi): ?>

<!-- Transfer Tamamlandı Başlık -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div style="display:flex;align-items:center;gap:10px">
    <div style="width:32px;height:32px;background:rgba(34,197,94,0.15);border-radius:50%;display:flex;align-items:center;justify-content:center">
      <i class="bi bi-check-circle-fill" style="color:var(--success);font-size:16px"></i>
    </div>
    <span style="font-size:17px;font-weight:700;color:var(--success)">Transfer Tamamlandı</span>
  </div>
  <button onclick="yazdir()" class="btn-accent" style="gap:8px">
    <i class="bi bi-printer"></i> <?= $lang=='en' ? 'Print Label' : ($lang=='de' ? 'Etikett drucken' : 'Etiket Yazdır') ?>
  </button>
</div>

<!-- Etiket Kartı -->
<div id="etiket-karti" style="max-width:680px;background:#fff;border-radius:14px;overflow:hidden;box-shadow:0 8px 32px rgba(0,0,0,0.3)">

  <!-- Turuncu başlık -->
  <div style="background:#f97316;padding:14px 24px;display:flex;align-items:center;justify-content:space-between">
    <div style="display:flex;align-items:center;gap:10px">
      <i class="bi bi-box-seam-fill" style="color:#fff;font-size:18px"></i>
      <span style="font-family:'Space Mono',monospace;font-size:13px;font-weight:700;color:#fff;letter-spacing:1px">
        DEPO TRANSFER ETİKETİ
      </span>
    </div>
    <span style="font-family:'Space Mono',monospace;font-size:12px;color:rgba(255,255,255,0.85)">
      <?= htmlspecialchars($etiket['trf_no']) ?>
    </span>
  </div>

  <!-- İçerik -->
  <div style="padding:24px;background:#fff">

    <!-- Ürün adı & barkod -->
    <div style="margin-bottom:20px">
      <div style="font-size:22px;font-weight:700;color:#111;font-family:'Space Mono',monospace;margin-bottom:4px">
        <?= htmlspecialchars($etiket['urun_adi']) ?>
      </div>
      <div style="font-size:13px;color:#666;font-family:'Space Mono',monospace">
        Barkod: <?= htmlspecialchars($etiket['barkod']) ?>
      </div>
    </div>

    <!-- Kaynak → Hedef -->
    <div style="background:#f8f9fa;border-radius:10px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
      <div>
        <div style="font-size:10px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;font-family:'Space Mono',monospace">KAYNAK</div>
        <div style="font-size:16px;font-weight:700;color:#111;font-family:'Space Mono',monospace"><?= htmlspecialchars($etiket['kaynak']) ?></div>
      </div>
      <i class="bi bi-arrow-right" style="font-size:22px;color:#f97316"></i>
      <div style="text-align:right">
        <div style="font-size:10px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;font-family:'Space Mono',monospace">HEDEF</div>
        <div style="font-size:16px;font-weight:700;color:#111;font-family:'Space Mono',monospace"><?= htmlspecialchars($etiket['hedef']) ?></div>
      </div>
    </div>

    <!-- Miktar + Tarih/Kullanıcı -->
    <div style="display:flex;align-items:flex-end;justify-content:space-between">
      <div>
        <div style="font-size:10px;font-weight:700;color:#999;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;font-family:'Space Mono',monospace">MİKTAR</div>
        <div style="display:flex;align-items:baseline;gap:6px">
          <span style="font-size:38px;font-weight:700;color:#f97316;font-family:'Space Mono',monospace;line-height:1"><?= htmlspecialchars($etiket['miktar']) ?></span>
          <span style="font-size:14px;color:#666">adet</span>
        </div>
      </div>
      <div style="text-align:right">
        <div style="font-size:13px;color:#555;margin-bottom:4px;display:flex;align-items:center;gap:6px;justify-content:flex-end">
          <i class="bi bi-calendar3" style="color:#999"></i>
          <span style="font-family:'Space Mono',monospace"><?= htmlspecialchars($etiket['tarih']) ?></span>
        </div>
        <div style="font-size:13px;color:#555;display:flex;align-items:center;gap:6px;justify-content:flex-end">
          <i class="bi bi-person" style="color:#999"></i>
          <span style="font-family:'Space Mono',monospace"><?= htmlspecialchars($etiket['kullanici']) ?></span>
        </div>
      </div>
    </div>

  </div>

  <!-- Alt footer -->
  <div style="border-top:1px dashed #ddd;padding:12px 24px;display:flex;justify-content:space-between;align-items:center;background:#fff">
    <span style="font-size:11px;color:#aaa;font-family:'Space Mono',monospace">Stok Takip Sistemi © 2026</span>
    <span style="font-size:11px;color:#aaa;font-family:'Space Mono',monospace"><?= htmlspecialchars($etiket['trf_no']) ?></span>
  </div>

</div>

<!-- Yeni transfer butonu -->
<div style="margin-top:20px">
  <a href="transfer.php" style="font-size:13px;color:var(--text-muted);text-decoration:none">
    ← <?= $lang=='en'?'New Transfer':($lang=='de'?'Neuer Transfer':'Yeni Transfer') ?>
  </a>
</div>

<style>
@media print {
  .sidebar, .main-wrapper > .top-bar, .page-heading, .page-subheading,
  div[style*="Transfer Tamamlandı"], div[style*="margin-top:20px"] { display:none!important; }
  .main-wrapper { margin-left:0!important; }
  .page-content { padding:0!important; }
  #etiket-karti { box-shadow:none!important; border:1px solid #ddd; }
  body { background:#fff!important; }
}
</style>

<script>
function yazdir(){
  // Sadece etiket kartını yazdır
  const icerik = document.getElementById('etiket-karti').outerHTML;
  const w = window.open('','','width=720,height=500');
  w.document.write(`
    <html><head><title>Transfer Etiketi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
    <style>body{margin:20px;background:#fff;font-family:sans-serif;}</style>
    </head><body>${icerik}<script>window.onload=()=>window.print()<\/script></body></html>
  `);
  w.document.close();
}
</script>

<?php else: ?>

<!-- Form -->
<div class="card" style="max-width:520px;padding:32px">
  <form method="post">
    <div style="margin-bottom:20px">
      <label class="form-label"><?= $T['lbl_product'] ?></label>
      <select class="form-control form-select" name="urun">
        <?php $u=$baglanti->query("SELECT * FROM urunler"); while($x=$u->fetch_assoc()): ?>
        <option value="<?= $x['id'] ?>"><?= htmlspecialchars($x['urun_adi']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px">
      <div>
        <label class="form-label"><?= $T['lbl_source'] ?></label>
        <select class="form-control form-select" name="kaynak">
          <?php foreach($depolar as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['depo_adi']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="form-label"><?= $T['lbl_target'] ?></label>
        <select class="form-control form-select" name="hedef">
          <?php foreach($depolar as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['depo_adi']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="margin-bottom:28px">
      <label class="form-label"><?= $T['lbl_quantity'] ?></label>
      <input class="form-control" name="miktar" type="number" placeholder="0" min="1" required>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
      <button type="submit" class="btn-accent" style="background:var(--info);box-shadow:0 4px 15px rgba(56,189,248,0.3)">
        <i class="bi bi-arrow-left-right"></i> <?= $T['btn_transfer'] ?>
      </button>
      <a href="hareketler.php" style="font-size:13px;color:var(--text-muted);text-decoration:none"><?= $T['view_movements'] ?></a>
    </div>
  </form>
</div>

<?php endif; ?>

<?php include "footer.php"; ?>
