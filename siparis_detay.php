<?php
// ── POST/redirect işlemleri header.php'den ÖNCE ──
if(session_status()===PHP_SESSION_NONE) session_start();
include "db.php";

$id = intval($_GET['id'] ?? 0);
if(!$id){ header("Location: siparisler.php"); exit(); }

// Durum güncelle
if($_POST && isset($_POST['yeni_durum'])){
    $durum = $baglanti->real_escape_string($_POST['yeni_durum']);
    $baglanti->query("UPDATE siparisler SET durum='$durum' WHERE id='$id'");

    // Teslim edildi → stok güncelle
    if($durum === 'teslim_edildi'){
        $s = $baglanti->query("SELECT * FROM siparisler WHERE id='$id'")->fetch_assoc();
        $kalemleri = $baglanti->query("SELECT * FROM siparis_kalemleri WHERE siparis_id='$id'");
        while($k = $kalemleri->fetch_assoc()){
            $baglanti->query("
                INSERT INTO stok(urun_id,depo_id,miktar) VALUES('{$k['urun_id']}','{$s['depo_id']}','{$k['miktar']}')
                ON DUPLICATE KEY UPDATE miktar=miktar+{$k['miktar']}
            ");
            $baglanti->query("
                INSERT INTO hareketler(urun_id,depo_id,islem,miktar)
                VALUES('{$k['urun_id']}','{$s['depo_id']}','stok_giris','{$k['miktar']}')
            ");
        }
    }
    header("Location: siparis_detay.php?id=$id&guncellendi=1");
    exit();
}

// ── Bundan sonra normal sayfa render ──
include "header.php";

// Sipariş bilgilerini çek
$siparis = $baglanti->query("SELECT s.*, d.depo_adi FROM siparisler s LEFT JOIN depolar d ON s.depo_id=d.id WHERE s.id='$id'")->fetch_assoc();
if(!$siparis){ echo "<p>Sipariş bulunamadı.</p>"; include "footer.php"; exit(); }

// Kalemleri çek
$kalemleri_q = $baglanti->query("
    SELECT k.*, u.urun_adi, u.barkod
    FROM siparis_kalemleri k
    JOIN urunler u ON k.urun_id = u.id
    WHERE k.siparis_id = '$id'
");
$kalemleri = [];
while($r = $kalemleri_q->fetch_assoc()) $kalemleri[] = $r;

$durum_map = [
  'beklemede'     => ['label' => $T['status_pending'],   'color' => 'var(--warning)', 'bg' => 'rgba(250,204,21,0.12)',  'icon' => 'bi-clock'],
  'onaylandi'     => ['label' => $T['status_approved'],  'color' => 'var(--info)',    'bg' => 'rgba(56,189,248,0.12)', 'icon' => 'bi-check-circle'],
  'teslim_edildi' => ['label' => $T['status_delivered'], 'color' => 'var(--success)', 'bg' => 'rgba(34,197,94,0.12)',  'icon' => 'bi-box-seam-fill'],
  'iptal'         => ['label' => $T['status_cancelled'], 'color' => 'var(--danger)',  'bg' => 'rgba(244,63,94,0.12)',  'icon' => 'bi-x-circle'],
];
$d = $durum_map[$siparis['durum']] ?? $durum_map['beklemede'];
?>

<!-- Başlık -->
<div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:28px;flex-wrap:wrap;gap:14px">
  <div>
    <div class="page-heading">
      <?= $T['order_detail_h'] ?> <span>#<?= str_pad($siparis['id'],5,'0',STR_PAD_LEFT) ?></span>
    </div>
    <div class="page-subheading"><?= htmlspecialchars($siparis['tedarikci']) ?> · <?= date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi'])) ?></div>
  </div>
  <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span style="background:<?= $d['bg'] ?>;color:<?= $d['color'] ?>;padding:7px 16px;border-radius:20px;font-size:13px;font-weight:700;font-family:'Space Mono',monospace">
      <i class="bi <?= $d['icon'] ?>"></i> <?= $d['label'] ?>
    </span>
    <button onclick="yazdir()" class="btn-accent" style="padding:7px 16px;font-size:13px">
      <i class="bi bi-printer"></i> <?= $T['btn_print_order'] ?>
    </button>
    <a href="siparisler.php" style="padding:7px 16px;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;font-size:13px;color:var(--text-muted);text-decoration:none;font-weight:600">
      ← <?= $T['back_to_orders'] ?>
    </a>
  </div>
</div>

<?php if(isset($_GET['yeni'])): ?>
<div class="alert alert-success" style="margin-bottom:20px"><i class="bi bi-check-circle"></i> <?= $T['order_created'] ?></div>
<?php endif; ?>
<?php if(isset($_GET['guncellendi'])): ?>
<div class="alert alert-success" style="margin-bottom:20px"><i class="bi bi-check-circle"></i> <?= $T['order_updated'] ?></div>
<?php endif; ?>

<div class="row g-3 mb-3">
  <!-- Sipariş bilgi kartı -->
  <div class="col-md-4">
    <div class="card" style="padding:24px;height:100%">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;font-family:'Space Mono',monospace">
        <?= $T['order_info_title'] ?>
      </div>
      <?php
      $bilgiler = [
        ['icon'=>'bi-shop',      'label'=>$T['col_supplier'],   'val'=>$siparis['tedarikci']],
        ['icon'=>'bi-building',  'label'=>$T['col_dest_depot'], 'val'=>$siparis['depo_adi'] ?? '-'],
        ['icon'=>'bi-calendar3', 'label'=>$T['col_date'],       'val'=>date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi']))],
        ['icon'=>'bi-person',    'label'=>$T['order_by'],       'val'=>$_SESSION['username'] ?? '-'],
      ];
      foreach($bilgiler as $b): ?>
      <div style="display:flex;align-items:flex-start;gap:12px;margin-bottom:14px">
        <div style="width:30px;height:30px;background:var(--bg-secondary);border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
          <i class="bi <?= $b['icon'] ?>" style="color:var(--accent);font-size:14px"></i>
        </div>
        <div>
          <div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;font-family:'Space Mono',monospace"><?= $b['label'] ?></div>
          <div style="font-weight:600;font-size:14px;margin-top:2px"><?= htmlspecialchars($b['val']) ?></div>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if($siparis['notlar']): ?>
      <div style="margin-top:8px;padding:12px;background:var(--bg-secondary);border-radius:8px;font-size:13px;color:var(--text-muted);line-height:1.5">
        <i class="bi bi-chat-text" style="margin-right:6px"></i><?= htmlspecialchars($siparis['notlar']) ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Durum güncelleme kartı -->
  <div class="col-md-4">
    <div class="card" style="padding:24px;height:100%">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;font-family:'Space Mono',monospace">
        <?= $T['order_status_title'] ?>
      </div>

      <?php
      $adimlar  = [
        'beklemede'     => ['icon'=>'bi-clock',         'label'=>$T['status_pending'],  'color'=>'var(--warning)'],
        'onaylandi'     => ['icon'=>'bi-check-circle',  'label'=>$T['status_approved'], 'color'=>'var(--info)'],
        'teslim_edildi' => ['icon'=>'bi-box-seam-fill', 'label'=>$T['status_delivered'],'color'=>'var(--success)'],
      ];
      $siralama   = ['beklemede','onaylandi','teslim_edildi'];
      $mevcut_idx = array_search($siparis['durum'], $siralama);
      ?>
      <div style="margin-bottom:20px">
        <?php foreach($siralama as $i => $akim): $a = $adimlar[$akim];
          $tamamlandi = ($mevcut_idx !== false && $i <= $mevcut_idx);
          $aktif      = ($siparis['durum'] === $akim);
        ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:<?= $i<2?'0':'0' ?>px">
          <div style="width:28px;height:28px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:13px;
                      background:<?= $tamamlandi ? $a['color'] : 'var(--bg-secondary)' ?>;
                      color:<?= $tamamlandi ? '#fff' : 'var(--text-muted)' ?>">
            <i class="bi <?= $tamamlandi && !$aktif ? 'bi-check-lg' : $a['icon'] ?>"></i>
          </div>
          <span style="font-size:13px;font-weight:<?= $aktif?'700':'500' ?>;color:<?= $tamamlandi ? 'var(--text-primary)' : 'var(--text-muted)' ?>">
            <?= $a['label'] ?>
          </span>
        </div>
        <?php if($i < 2): ?>
        <div style="width:2px;height:14px;margin-left:13px;margin-top:4px;margin-bottom:4px;background:var(--border)"></div>
        <?php endif; ?>
        <?php endforeach; ?>
        <?php if($siparis['durum']==='iptal'): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-top:8px">
          <div style="width:28px;height:28px;border-radius:50%;background:var(--danger);display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px">
            <i class="bi bi-x-lg"></i>
          </div>
          <span style="font-size:13px;font-weight:700;color:var(--danger)"><?= $T['status_cancelled'] ?></span>
        </div>
        <?php endif; ?>
      </div>

      <?php if($siparis['durum'] !== 'teslim_edildi' && $siparis['durum'] !== 'iptal'): ?>
      <div style="display:flex;flex-direction:column;gap:8px">
        <?php if($siparis['durum']==='beklemede'): ?>
        <form method="post">
          <input type="hidden" name="yeni_durum" value="onaylandi">
          <button type="submit" class="btn-accent" style="width:100%;background:var(--info);box-shadow:0 4px 12px rgba(56,189,248,0.25);justify-content:center">
            <i class="bi bi-check-circle"></i> <?= $T['btn_approve'] ?>
          </button>
        </form>
        <?php endif; ?>
        <?php if($siparis['durum']==='onaylandi'): ?>
        <form method="post">
          <input type="hidden" name="yeni_durum" value="teslim_edildi">
          <button type="submit" class="btn-accent" style="width:100%;background:var(--success);box-shadow:0 4px 12px rgba(34,197,94,0.25);justify-content:center">
            <i class="bi bi-box-seam-fill"></i> <?= $T['btn_deliver'] ?>
          </button>
        </form>
        <?php endif; ?>
        <form method="post">
          <input type="hidden" name="yeni_durum" value="iptal">
          <button type="submit" style="width:100%;padding:10px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.2);border-radius:8px;color:var(--danger);font-size:13px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
            <i class="bi bi-x-circle"></i> <?= $T['btn_cancel_order'] ?>
          </button>
        </form>
      </div>
      <?php endif; ?>
      <?php if($siparis['durum']==='teslim_edildi'): ?>
      <div class="alert alert-success" style="margin:0">
        <i class="bi bi-box-seam-fill"></i> <?= $T['delivered_note'] ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Özet kartı -->
  <div class="col-md-4">
    <div class="card" style="padding:24px;height:100%">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:16px;font-family:'Space Mono',monospace">
        <?= $T['order_summary_title'] ?>
      </div>
      <?php
      $toplam_kalem  = count($kalemleri);
      $toplam_miktar = array_sum(array_column($kalemleri, 'miktar'));
      ?>
      <div style="display:flex;flex-direction:column;gap:12px">
        <div style="padding:14px;background:var(--bg-secondary);border-radius:10px;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:13px;color:var(--text-muted)"><?= $T['total_items'] ?></span>
          <span style="font-family:'Space Mono',monospace;font-weight:700;font-size:18px;color:var(--accent)"><?= $toplam_kalem ?></span>
        </div>
        <div style="padding:14px;background:var(--bg-secondary);border-radius:10px;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:13px;color:var(--text-muted)"><?= $T['total_qty'] ?></span>
          <span style="font-family:'Space Mono',monospace;font-weight:700;font-size:18px;color:var(--text-primary)"><?= number_format($toplam_miktar) ?> <span style="font-size:12px;font-weight:400"><?= $T['unit'] ?></span></span>
        </div>
        <div style="padding:14px;background:var(--bg-secondary);border-radius:10px;display:flex;justify-content:space-between;align-items:center">
          <span style="font-size:13px;color:var(--text-muted)"><?= $T['order_no'] ?></span>
          <span style="font-family:'Space Mono',monospace;font-weight:700;font-size:14px;color:var(--text-primary)">#<?= str_pad($siparis['id'],5,'0',STR_PAD_LEFT) ?></span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Kalemler Tablosu -->
<div id="siparis-detay-print">
  <div class="print-header" style="display:none;margin-bottom:20px;padding-bottom:14px;border-bottom:2px solid #ddd">
    <div style="font-size:20px;font-weight:700;color:#111;font-family:monospace">SİPARİŞ #<?= str_pad($siparis['id'],5,'0',STR_PAD_LEFT) ?></div>
    <div style="font-size:13px;color:#666;margin-top:4px"><?= htmlspecialchars($siparis['tedarikci']) ?> · <?= date('d.m.Y H:i', strtotime($siparis['olusturma_tarihi'])) ?> · <?= htmlspecialchars($siparis['depo_adi'] ?? '') ?></div>
  </div>

  <div class="table-wrapper">
    <table class="table">
      <thead>
        <tr>
          <th style="width:50px">#</th>
          <th><?= $T['col_product'] ?></th>
          <th><?= $T['col_barcode'] ?></th>
          <th style="text-align:right"><?= $T['col_quantity'] ?></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($kalemleri as $i => $k): ?>
        <tr>
          <td><span style="font-family:'Space Mono',monospace;font-size:12px;color:var(--text-muted)"><?= $i+1 ?></span></td>
          <td style="font-weight:600"><?= htmlspecialchars($k['urun_adi']) ?></td>
          <td>
            <span style="display:inline-flex;align-items:center;gap:5px;font-family:'Space Mono',monospace;font-size:12px;background:var(--accent-dim);padding:4px 10px;border-radius:16px;color:var(--accent)">
              <i class="bi bi-upc-scan"></i><?= htmlspecialchars($k['barkod']) ?>
            </span>
          </td>
          <td style="text-align:right">
            <span style="font-family:'Space Mono',monospace;font-weight:700;font-size:16px"><?= $k['miktar'] ?></span>
            <span style="font-size:12px;color:var(--text-muted)"> <?= $T['unit'] ?></span>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" style="text-align:right;font-size:12px;color:var(--text-muted);font-family:'Space Mono',monospace;padding-top:14px"><?= $T['total_qty'] ?></td>
          <td style="text-align:right;font-family:'Space Mono',monospace;font-weight:700;font-size:18px;color:var(--accent);padding-top:14px"><?= number_format($toplam_miktar) ?></td>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<style>
@media print {
  .sidebar,.main-wrapper>.top-bar,.row.g-3.mb-3,.alert,
  div[style*="display:flex;align-items:flex-start;justify-content:space-between"]{display:none!important;}
  .main-wrapper{margin-left:0!important;}
  .page-content{padding:0!important;}
  body{background:#fff!important;color:#111!important;}
  .print-header{display:block!important;}
  .table-wrapper{border:1px solid #ddd!important;}
}
</style>
<script>
function yazdir(){
  document.querySelector('.print-header').style.display='block';
  window.print();
  setTimeout(()=>{ document.querySelector('.print-header').style.display='none'; },600);
}
</script>

<?php include "footer.php"; ?>
