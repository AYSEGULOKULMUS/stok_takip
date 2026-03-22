<?php
// ── POST/redirect işlemleri header.php'den ÖNCE ──
if(session_status()===PHP_SESSION_NONE) session_start();
include "db.php";

$mesaj = null; $mesaj_tip = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $tedarikci = trim($baglanti->real_escape_string($_POST['tedarikci']));
    $depo_id   = intval($_POST['depo_id']);
    $notlar    = trim($baglanti->real_escape_string($_POST['notlar'] ?? ''));
    $urunler   = $_POST['urun_id']  ?? [];
    $miktarlar = $_POST['miktar']   ?? [];

    $depo_row = $baglanti->query("SELECT depo_adi FROM depolar WHERE id='$depo_id'")->fetch_assoc();
    $hedef_depo_adi = $depo_row['depo_adi'] ?? '';

    if(empty($tedarikci) || empty($urunler)){
        $mesaj = 'err_required'; // placeholder — $T henüz yok
        $mesaj_tip = 'danger';
    } else {
        $kullanici_id = 0;
        $u = $baglanti->query("SELECT id FROM users WHERE username='" . $baglanti->real_escape_string($_SESSION['username'] ?? '') . "'");
        if($u && $u->num_rows > 0) $kullanici_id = $u->fetch_assoc()['id'];

        $baglanti->query("
            INSERT INTO siparisler(tedarikci, depo_id, hedef_depo_adi, notlar, durum, kullanici_id, olusturma_tarihi)
            VALUES('$tedarikci', '$depo_id', '$hedef_depo_adi', '$notlar', 'beklemede', '$kullanici_id', NOW())
        ");
        $siparis_id = $baglanti->insert_id;

        foreach($urunler as $idx => $urun_id){
            $urun_id = intval($urun_id);
            $miktar  = intval($miktarlar[$idx] ?? 0);
            if($urun_id > 0 && $miktar > 0){
                $baglanti->query("INSERT INTO siparis_kalemleri(siparis_id, urun_id, miktar) VALUES('$siparis_id','$urun_id','$miktar')");
            }
        }

        header("Location: siparis_detay.php?id=$siparis_id&yeni=1");
        exit();
    }
}

// ── Bundan sonra normal sayfa render ──
include "header.php";

// Hata mesajını $T ile çevir (POST'ta $mesaj 'err_required' placeholder ise)
if($mesaj === 'err_required') $mesaj = $T['order_err_required'];

// Ürün ve depo listelerini çek
$urunler_q = $baglanti->query("SELECT * FROM urunler ORDER BY urun_adi");
$urunler_list = [];
while($r = $urunler_q->fetch_assoc()) $urunler_list[] = $r;

$depolar_q = $baglanti->query("SELECT * FROM depolar ORDER BY depo_adi");
$depolar_list = [];
while($r = $depolar_q->fetch_assoc()) $depolar_list[] = $r;
?>

<div style="margin-bottom:28px">
  <div class="page-heading"><?= $T['new_order_h1'] ?> <span><?= $T['new_order_h2'] ?></span></div>
  <div class="page-subheading"><?= $T['new_order_sub'] ?></div>
</div>

<?php if($mesaj): ?>
<div class="alert alert-<?= $mesaj_tip ?>" style="margin-bottom:24px">
  <i class="bi bi-exclamation-circle"></i> <?= $mesaj ?>
</div>
<?php endif; ?>

<form method="post" id="siparis-form">

  <!-- Temel Bilgiler -->
  <div class="card" style="max-width:680px;padding:32px;margin-bottom:20px">
    <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:20px;font-family:'Space Mono',monospace">
      <i class="bi bi-info-circle" style="color:var(--accent);margin-right:6px"></i> <?= $T['order_basic_info'] ?>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:18px;margin-bottom:18px">
      <div>
        <label class="form-label"><?= $T['col_supplier'] ?></label>
        <input class="form-control" name="tedarikci" placeholder="<?= $T['ph_supplier'] ?>" required>
      </div>
      <div>
        <label class="form-label"><?= $T['col_dest_depot'] ?></label>
        <select class="form-control form-select" name="depo_id" required>
          <option value="">— <?= $T['select_depot'] ?> —</option>
          <?php foreach($depolar_list as $d): ?>
          <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['depo_adi']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div>
      <label class="form-label"><?= $T['lbl_order_notes'] ?> <span style="color:var(--text-muted);font-size:11px;text-transform:none;letter-spacing:0"><?= $T['lbl_optional'] ?></span></label>
      <textarea class="form-control" name="notlar" placeholder="<?= $T['ph_order_notes'] ?>" rows="2" style="resize:vertical"></textarea>
    </div>
  </div>

  <!-- Sipariş Kalemleri -->
  <div class="card" style="max-width:680px;padding:32px;margin-bottom:24px">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px">
      <div style="font-size:12px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;font-family:'Space Mono',monospace">
        <i class="bi bi-list-ul" style="color:var(--accent);margin-right:6px"></i> <?= $T['order_items_title'] ?>
      </div>
      <button type="button" onclick="kalemEkle()" class="btn-accent" style="padding:7px 16px;font-size:13px">
        <i class="bi bi-plus-lg"></i> <?= $T['btn_add_item'] ?>
      </button>
    </div>

    <div id="kalemler">
      <div class="kalem-row" style="display:grid;grid-template-columns:1fr 130px 36px;gap:10px;align-items:center;margin-bottom:10px">
        <select class="form-control form-select" name="urun_id[]" required>
          <option value="">— <?= $T['select_product'] ?> —</option>
          <?php foreach($urunler_list as $u): ?>
          <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['urun_adi']) ?> · <?= htmlspecialchars($u['barkod']) ?></option>
          <?php endforeach; ?>
        </select>
        <input class="form-control" name="miktar[]" type="number" placeholder="<?= $T['lbl_quantity'] ?>" min="1" required
               style="text-align:center;font-family:'Space Mono',monospace;font-weight:700">
        <button type="button" onclick="kalemSil(this)"
                style="width:34px;height:38px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.2);border-radius:8px;color:var(--danger);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center">
          <i class="bi bi-trash"></i>
        </button>
      </div>
    </div>

    <div style="font-size:12px;color:var(--text-muted);margin-top:8px;font-family:'Space Mono',monospace">
      <i class="bi bi-info-circle"></i> <?= $T['order_items_hint'] ?>
    </div>
  </div>

  <div style="display:flex;gap:12px;align-items:center">
    <button type="submit" class="btn-accent">
      <i class="bi bi-cart-plus"></i> <?= $T['btn_create_order'] ?>
    </button>
    <a href="siparisler.php" style="font-size:13px;color:var(--text-muted);text-decoration:none">← <?= $T['back_to_orders'] ?></a>
  </div>
</form>

<script>
const urunOptions = `<?php
  $opts = '<option value="">— ' . addslashes($T['select_product']) . ' —</option>';
  foreach($urunler_list as $u){
    $opts .= '<option value="' . $u['id'] . '">' . htmlspecialchars($u['urun_adi'], ENT_QUOTES) . ' · ' . htmlspecialchars($u['barkod'], ENT_QUOTES) . '</option>';
  }
  echo $opts;
?>`;
const miktarPlaceholder = '<?= addslashes($T['lbl_quantity']) ?>';

function kalemEkle(){
  const div = document.createElement('div');
  div.className = 'kalem-row';
  div.style.cssText = 'display:grid;grid-template-columns:1fr 130px 36px;gap:10px;align-items:center;margin-bottom:10px';
  div.innerHTML = `
    <select class="form-control form-select" name="urun_id[]" required>${urunOptions}</select>
    <input class="form-control" name="miktar[]" type="number" placeholder="${miktarPlaceholder}" min="1" required
           style="text-align:center;font-family:'Space Mono',monospace;font-weight:700">
    <button type="button" onclick="kalemSil(this)"
            style="width:34px;height:38px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.2);border-radius:8px;color:var(--danger);font-size:16px;cursor:pointer;display:flex;align-items:center;justify-content:center">
      <i class="bi bi-trash"></i>
    </button>`;
  document.getElementById('kalemler').appendChild(div);
}

function kalemSil(btn){
  const rows = document.querySelectorAll('.kalem-row');
  if(rows.length > 1) btn.closest('.kalem-row').remove();
}
</script>

<?php include "footer.php"; ?>
