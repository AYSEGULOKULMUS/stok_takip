<?php
// ── POST/redirect işlemleri header.php'den ÖNCE ──
if(session_status()===PHP_SESSION_NONE) session_start();
include "db.php";

// Sipariş sil
if(isset($_GET['sil']) && is_numeric($_GET['sil'])){
    $sid = intval($_GET['sil']);
    $baglanti->query("DELETE FROM siparis_kalemleri WHERE siparis_id='$sid'");
    $baglanti->query("DELETE FROM siparisler WHERE id='$sid'");
    header("Location: siparisler.php?silindi=1");
    exit();
}

// Durum güncelle
if($_POST && isset($_POST['durum_guncelle'])){
    $sid   = intval($_POST['siparis_id']);
    $durum = $baglanti->real_escape_string($_POST['yeni_durum']);
    $baglanti->query("UPDATE siparisler SET durum='$durum' WHERE id='$sid'");
    header("Location: siparisler.php?guncellendi=1");
    exit();
}

// ── Bundan sonra normal sayfa render ──
include "header.php";

// Filtre
$filtre_durum = $_GET['durum'] ?? '';
$filtre_where = $filtre_durum ? "WHERE s.durum='" . $baglanti->real_escape_string($filtre_durum) . "'" : '';

$sorgu = $baglanti->query("
    SELECT s.*, u.username AS kullanici_adi,
           COUNT(k.id) AS kalem_sayisi,
           SUM(k.miktar) AS toplam_miktar
    FROM siparisler s
    LEFT JOIN users u ON s.kullanici_id = u.id
    LEFT JOIN siparis_kalemleri k ON k.siparis_id = s.id
    $filtre_where
    GROUP BY s.id
    ORDER BY s.olusturma_tarihi DESC
");
?>

<div style="margin-bottom:28px;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <div class="page-heading"><?= $T['order_list_h1'] ?> <span><?= $T['order_list_h2'] ?></span></div>
    <div class="page-subheading"><?= $T['order_list_sub'] ?></div>
  </div>
  <a href="siparis_ekle.php" class="btn-accent">
    <i class="bi bi-plus-lg"></i> <?= $T['btn_new_order'] ?>
  </a>
</div>

<?php if(isset($_GET['silindi'])): ?>
<div class="alert alert-danger" style="margin-bottom:20px"><i class="bi bi-trash"></i> <?= $T['order_deleted'] ?></div>
<?php endif; ?>
<?php if(isset($_GET['guncellendi'])): ?>
<div class="alert alert-success" style="margin-bottom:20px"><i class="bi bi-check-circle"></i> <?= $T['order_updated'] ?></div>
<?php endif; ?>

<!-- Filtre bar -->
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
  <?php
  $durumlar = [
    ''              => ['label' => $T['filter_all'],       'color' => 'var(--text-muted)', 'bg' => 'var(--bg-card)'],
    'beklemede'     => ['label' => $T['status_pending'],   'color' => 'var(--warning)',    'bg' => 'rgba(250,204,21,0.12)'],
    'onaylandi'     => ['label' => $T['status_approved'],  'color' => 'var(--info)',       'bg' => 'rgba(56,189,248,0.12)'],
    'teslim_edildi' => ['label' => $T['status_delivered'], 'color' => 'var(--success)',    'bg' => 'rgba(34,197,94,0.12)'],
    'iptal'         => ['label' => $T['status_cancelled'], 'color' => 'var(--danger)',     'bg' => 'rgba(244,63,94,0.12)'],
  ];
  foreach($durumlar as $kod => $d):
    $aktif = ($filtre_durum === $kod);
  ?>
  <a href="siparisler.php<?= $kod ? '?durum='.$kod : '' ?>"
     style="padding:7px 16px;border-radius:20px;font-size:12px;font-weight:700;text-decoration:none;
            font-family:'Space Mono',monospace;border:1px solid var(--border);transition:all .15s;
            background:<?= $aktif ? $d['bg'] : 'var(--bg-card)' ?>;
            color:<?= $aktif ? $d['color'] : 'var(--text-muted)' ?>;
            border-color:<?= $aktif ? $d['color'] : 'var(--border)' ?>">
    <?= $d['label'] ?>
  </a>
  <?php endforeach; ?>
</div>

<div class="table-wrapper">
  <table class="table">
    <thead>
      <tr>
        <th><?= $T['col_order_no'] ?></th>
        <th><?= $T['col_supplier'] ?></th>
        <th><?= $T['col_items'] ?></th>
        <th><?= $T['col_dest_depot'] ?></th>
        <th><?= $T['col_status'] ?></th>
        <th><?= $T['col_date'] ?></th>
        <th><?= $T['col_actions'] ?></th>
      </tr>
    </thead>
    <tbody>
      <?php if($sorgu->num_rows === 0): ?>
      <tr>
        <td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted)">
          <i class="bi bi-inbox" style="font-size:28px;display:block;margin-bottom:8px;opacity:.4"></i>
          <?= $T['no_orders'] ?>
        </td>
      </tr>
      <?php else: while($row = $sorgu->fetch_assoc()): ?>
      <tr>
        <td>
          <a href="siparis_detay.php?id=<?= $row['id'] ?>" style="text-decoration:none">
            <span style="font-family:'Space Mono',monospace;font-size:12px;font-weight:700;color:var(--accent)">
              #<?= str_pad($row['id'],5,'0',STR_PAD_LEFT) ?>
            </span>
          </a>
        </td>
        <td>
          <div style="font-weight:600;font-size:14px"><?= htmlspecialchars($row['tedarikci']) ?></div>
          <?php if($row['notlar']): ?>
          <div style="font-size:11px;color:var(--text-muted);margin-top:2px;max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
            <?= htmlspecialchars($row['notlar']) ?>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <span style="font-family:'Space Mono',monospace;font-weight:700"><?= $row['kalem_sayisi'] ?></span>
          <span style="font-size:11px;color:var(--text-muted)"> <?= $T['unit_items'] ?></span>
          <br>
          <span style="font-size:12px;color:var(--text-muted)"><?= number_format($row['toplam_miktar'] ?? 0) ?> <?= $T['unit'] ?></span>
        </td>
        <td style="font-size:13px;color:var(--text-muted)"><?= htmlspecialchars($row['hedef_depo_adi'] ?? '-') ?></td>
        <td>
          <?php
          $durum_map = [
            'beklemede'     => ['badge' => 'warning', 'icon' => 'bi-clock',          'label' => $T['status_pending']],
            'onaylandi'     => ['badge' => 'info',    'icon' => 'bi-check-circle',   'label' => $T['status_approved']],
            'teslim_edildi' => ['badge' => 'success', 'icon' => 'bi-box-seam-fill',  'label' => $T['status_delivered']],
            'iptal'         => ['badge' => 'danger',  'icon' => 'bi-x-circle',       'label' => $T['status_cancelled']],
          ];
          $d = $durum_map[$row['durum']] ?? $durum_map['beklemede'];
          $colors = ['warning'=>'var(--warning)','info'=>'var(--info)','success'=>'var(--success)','danger'=>'var(--danger)'];
          $bgs    = ['warning'=>'rgba(250,204,21,0.12)','info'=>'rgba(56,189,248,0.12)','success'=>'rgba(34,197,94,0.12)','danger'=>'rgba(244,63,94,0.12)'];
          $c  = $colors[$d['badge']];
          $bg = $bgs[$d['badge']];
          ?>
          <span style="background:<?= $bg ?>;color:<?= $c ?>;padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;font-family:'Space Mono',monospace;white-space:nowrap">
            <i class="bi <?= $d['icon'] ?>"></i> <?= $d['label'] ?>
          </span>
        </td>
        <td style="font-size:13px;color:var(--text-muted);font-family:'Space Mono',monospace">
          <?= date('d.m.Y', strtotime($row['olusturma_tarihi'])) ?>
        </td>
        <td>
          <div style="display:flex;gap:6px;align-items:center">
            <a href="siparis_detay.php?id=<?= $row['id'] ?>"
               style="display:inline-flex;align-items:center;padding:5px 10px;background:var(--accent-dim);color:var(--accent);border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;gap:5px">
              <i class="bi bi-eye"></i> <?= $T['btn_view'] ?>
            </a>
            <?php if($row['durum']==='beklemede'): ?>
            <button onclick="hizliDurum(<?= $row['id'] ?>, 'onaylandi')"
                    style="display:inline-flex;align-items:center;padding:5px 10px;background:rgba(56,189,248,0.12);color:var(--info);border-radius:6px;font-size:12px;font-weight:600;border:none;cursor:pointer;gap:5px">
              <i class="bi bi-check-lg"></i> <?= $T['btn_approve'] ?>
            </button>
            <?php endif; ?>
            <?php if($row['durum']==='onaylandi'): ?>
            <button onclick="hizliDurum(<?= $row['id'] ?>, 'teslim_edildi')"
                    style="display:inline-flex;align-items:center;padding:5px 10px;background:rgba(34,197,94,0.12);color:var(--success);border-radius:6px;font-size:12px;font-weight:600;border:none;cursor:pointer;gap:5px">
              <i class="bi bi-box-arrow-in-down"></i> <?= $T['btn_deliver'] ?>
            </button>
            <?php endif; ?>
          </div>
        </td>
      </tr>
      <?php endwhile; endif; ?>
    </tbody>
  </table>
</div>

<form method="post" id="durum-form" style="display:none">
  <input type="hidden" name="durum_guncelle" value="1">
  <input type="hidden" name="siparis_id" id="f_siparis_id">
  <input type="hidden" name="yeni_durum" id="f_yeni_durum">
</form>

<script>
function hizliDurum(id, durum){
  document.getElementById('f_siparis_id').value = id;
  document.getElementById('f_yeni_durum').value  = durum;
  document.getElementById('durum-form').submit();
}
</script>

<?php include "footer.php"; ?>
