<?php
// ── Sil / Güncelle işlemleri header.php'den ÖNCE ──
if(session_status()===PHP_SESSION_NONE) session_start();
include "db.php";

// SİL
if(isset($_GET['sil']) && is_numeric($_GET['sil'])){
    $sid = intval($_GET['sil']);
    // Önce bağlı stok ve hareketleri sil, sonra ürünü sil
    $baglanti->query("DELETE FROM stok WHERE urun_id='$sid'");
    $baglanti->query("DELETE FROM hareketler WHERE urun_id='$sid'");
    $baglanti->query("DELETE FROM siparis_kalemleri WHERE urun_id='$sid'");
    $baglanti->query("DELETE FROM urunler WHERE id='$sid'");
    header("Location: urunler.php?silindi=1");
    exit();
}

// GÜNCELLE — POST
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['guncelle_id'])){
    $gid      = intval($_POST['guncelle_id']);
    $barkod   = $baglanti->real_escape_string(trim($_POST['barkod']));
    $urun_adi = $baglanti->real_escape_string(trim($_POST['urun_adi']));
    $aciklama = $baglanti->real_escape_string(trim($_POST['aciklama']));
    if(!empty($barkod) && !empty($urun_adi)){
        $baglanti->query("UPDATE urunler SET barkod='$barkod', urun_adi='$urun_adi', aciklama='$aciklama' WHERE id='$gid'");
        header("Location: urunler.php?guncellendi=1");
        exit();
    }
}

include "header.php";

$sonuc = $baglanti->query("SELECT * FROM urunler ORDER BY id ASC");
?>

<div style="margin-bottom:28px;display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px">
  <div>
    <div class="page-heading"><?= $T['product_list'] ?> <span><?= $T['product_list_h'] ?></span></div>
    <div class="page-subheading"><?= $T['product_list_sub'] ?></div>
  </div>
  <a href="urun_ekle.php" class="btn-accent"><i class="bi bi-plus-lg"></i> <?= $T['btn_new_product'] ?></a>
</div>

<?php if(isset($_GET['silindi'])): ?>
<div class="alert alert-danger" style="margin-bottom:20px"><i class="bi bi-trash"></i> <?= $T['product_deleted'] ?></div>
<?php endif; ?>
<?php if(isset($_GET['guncellendi'])): ?>
<div class="alert alert-success" style="margin-bottom:20px"><i class="bi bi-check-circle"></i> <?= $T['product_updated'] ?></div>
<?php endif; ?>

<div class="table-wrapper">
  <table class="table">
    <thead>
      <tr>
        <th><?= $T['col_id'] ?></th>
        <th><?= $T['col_barcode'] ?></th>
        <th><?= $T['col_product'] ?></th>
        <th><?= $T['col_desc'] ?></th>
        <th style="text-align:right;width:130px"><?= $T['col_actions'] ?></th>
      </tr>
    </thead>
    <tbody>
      <?php while($row = $sonuc->fetch_assoc()): ?>
      <tr id="row-<?= $row['id'] ?>">
        <!-- GÖRÜNTÜLEME MODU -->
        <td><span style="font-family:'Space Mono',monospace;font-size:12px;color:var(--text-muted)">#<?= str_pad($row['id'],3,'0',STR_PAD_LEFT) ?></span></td>
        <td>
          <span class="view-mode-<?= $row['id'] ?>" style="display:inline-flex;align-items:center;gap:6px;font-family:'Space Mono',monospace;font-size:12px;background:var(--accent-dim);border:1px solid rgba(249,115,22,0.3);padding:5px 10px;border-radius:20px;color:var(--accent);letter-spacing:0.5px">
            <i class="bi bi-upc-scan" style="font-size:13px"></i><?= htmlspecialchars($row['barkod']) ?>
          </span>
          <input class="form-control edit-mode-<?= $row['id'] ?>" name="barkod" style="display:none;width:160px;font-family:'Space Mono',monospace;font-size:13px;padding:6px 10px"
                 value="<?= htmlspecialchars($row['barkod']) ?>">
        </td>
        <td>
          <span class="view-mode-<?= $row['id'] ?>" style="font-weight:500"><?= htmlspecialchars($row['urun_adi']) ?></span>
          <input class="form-control edit-mode-<?= $row['id'] ?>" name="urun_adi" style="display:none;font-size:14px;padding:6px 10px"
                 value="<?= htmlspecialchars($row['urun_adi']) ?>">
        </td>
        <td>
          <span class="view-mode-<?= $row['id'] ?>" style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($row['aciklama']) ?></span>
          <input class="form-control edit-mode-<?= $row['id'] ?>" name="aciklama" style="display:none;font-size:13px;padding:6px 10px"
                 value="<?= htmlspecialchars($row['aciklama']) ?>">
        </td>
        <td style="text-align:right">
          <!-- Görüntüleme butonları -->
          <div class="view-mode-<?= $row['id'] ?>" style="display:inline-flex;gap:6px">
            <button onclick="editMod(<?= $row['id'] ?>)"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:var(--accent-dim);color:var(--accent);border:1px solid rgba(249,115,22,0.25);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer">
              <i class="bi bi-pencil"></i> <?= $T['btn_edit'] ?>
            </button>
            <button onclick="silOnayla(<?= $row['id'] ?>, '<?= htmlspecialchars(addslashes($row['urun_adi'])) ?>')"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:rgba(244,63,94,0.1);color:var(--danger);border:1px solid rgba(244,63,94,0.2);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer">
              <i class="bi bi-trash"></i> <?= $T['btn_delete'] ?>
            </button>
          </div>
          <!-- Düzenleme butonları -->
          <div class="edit-mode-<?= $row['id'] ?>" style="display:none;gap:6px">
            <button onclick="kaydetForm(<?= $row['id'] ?>)"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:rgba(34,197,94,0.12);color:var(--success);border:1px solid rgba(34,197,94,0.25);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer">
              <i class="bi bi-check-lg"></i> <?= $T['btn_save'] ?>
            </button>
            <button onclick="iptalMod(<?= $row['id'] ?>)"
                    style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:var(--bg-secondary);color:var(--text-muted);border:1px solid var(--border);border-radius:7px;font-size:12px;font-weight:600;cursor:pointer">
              <i class="bi bi-x-lg"></i> <?= $T['btn_cancel'] ?>
            </button>
          </div>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<!-- Gizli güncelleme formu -->
<form method="post" id="guncelle-form" style="display:none">
  <input type="hidden" name="guncelle_id" id="f_gid">
  <input type="hidden" name="barkod"   id="f_barkod">
  <input type="hidden" name="urun_adi" id="f_urun_adi">
  <input type="hidden" name="aciklama" id="f_aciklama">
</form>

<!-- Silme onay modalı -->
<div id="sil-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:999;align-items:center;justify-content:center;backdrop-filter:blur(4px)">
  <div style="background:var(--bg-card);border:1px solid var(--border);border-radius:16px;padding:32px;max-width:420px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,0.5)">
    <div style="width:52px;height:52px;background:rgba(244,63,94,0.12);border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:18px">
      <i class="bi bi-exclamation-triangle-fill" style="color:var(--danger);font-size:22px"></i>
    </div>
    <div style="font-family:'Space Mono',monospace;font-size:17px;font-weight:700;color:var(--text-primary);margin-bottom:8px"><?= $T['delete_confirm_title'] ?></div>
    <div style="font-size:13px;color:var(--text-muted);margin-bottom:6px"><?= $T['delete_confirm_msg'] ?></div>
    <div id="sil-urun-adi" style="font-size:14px;font-weight:700;color:var(--danger);margin-bottom:24px;font-family:'Space Mono',monospace"></div>
    <div style="font-size:12px;color:var(--text-muted);background:var(--bg-secondary);border-radius:8px;padding:10px 14px;margin-bottom:24px">
      <i class="bi bi-info-circle" style="margin-right:5px"></i><?= $T['delete_warn'] ?>
    </div>
    <div style="display:flex;gap:10px">
      <button onclick="silIptal()"
              style="flex:1;padding:11px;background:var(--bg-secondary);border:1px solid var(--border);border-radius:9px;color:var(--text-muted);font-size:14px;font-weight:600;cursor:pointer">
        <?= $T['btn_cancel'] ?>
      </button>
      <a id="sil-link" href="#"
         style="flex:1;padding:11px;background:rgba(244,63,94,0.15);border:1px solid rgba(244,63,94,0.3);border-radius:9px;color:var(--danger);font-size:14px;font-weight:600;cursor:pointer;text-align:center;text-decoration:none;display:flex;align-items:center;justify-content:center;gap:7px">
        <i class="bi bi-trash"></i> <?= $T['btn_delete_confirm'] ?>
      </a>
    </div>
  </div>
</div>

<script>
// ── Inline edit ──
function editMod(id){
  document.querySelectorAll('.view-mode-'+id).forEach(el => el.style.display='none');
  document.querySelectorAll('.edit-mode-'+id).forEach(el => { el.style.display=''; });
  // div için flex
  document.querySelector('#row-'+id+' .edit-mode-'+id+'.form-control') && null;
  const btns = document.querySelector('#row-'+id+' div.edit-mode-'+id);
  if(btns) btns.style.display='inline-flex';
}

function iptalMod(id){
  document.querySelectorAll('.view-mode-'+id).forEach(el => el.style.display='');
  document.querySelectorAll('.edit-mode-'+id).forEach(el => { el.style.display='none'; });
  const btns = document.querySelector('#row-'+id+' div.view-mode-'+id);
  if(btns) btns.style.display='inline-flex';
}

function kaydetForm(id){
  const row    = document.getElementById('row-'+id);
  const inputs = row.querySelectorAll('input.edit-mode-'+id);
  const vals   = {};
  inputs.forEach(inp => { vals[inp.name] = inp.value; });

  document.getElementById('f_gid').value      = id;
  document.getElementById('f_barkod').value   = vals['barkod']   || '';
  document.getElementById('f_urun_adi').value = vals['urun_adi'] || '';
  document.getElementById('f_aciklama').value = vals['aciklama'] || '';
  document.getElementById('guncelle-form').submit();
}

// ── Silme onayı ──
function silOnayla(id, ad){
  document.getElementById('sil-urun-adi').textContent = ad;
  document.getElementById('sil-link').href = 'urunler.php?sil='+id;
  const modal = document.getElementById('sil-modal');
  modal.style.display = 'flex';
}
function silIptal(){
  document.getElementById('sil-modal').style.display = 'none';
}
// Modal dışına tıklayınca kapat
document.getElementById('sil-modal').addEventListener('click', function(e){
  if(e.target === this) silIptal();
});
</script>

<?php include "footer.php"; ?>
