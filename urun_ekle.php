<?php
include "header.php";
include "db.php";

$mesaj = null; $mesaj_tip = null;

if($_SERVER['REQUEST_METHOD']==='POST'){
    $barkod = trim($_POST['barkod']);
    $ad     = trim($_POST['urun_adi']);
    $aciklama = trim($_POST['aciklama']);
    $init_depo  = $_POST['init_depo'] ?? '';
    $init_stok  = intval($_POST['init_stok'] ?? 0);
    if(empty($barkod)||empty($ad)){
        $mesaj=$T['err_required']; $mesaj_tip='danger';
    } else {
        $stmt=$baglanti->prepare("INSERT INTO urunler(barkod,urun_adi,aciklama) VALUES(?,?,?)");
        $stmt->bind_param("sss",$barkod,$ad,$aciklama);
        if($stmt->execute()){
            $yeni_id = $stmt->insert_id;
            // Depo ve başlangıç stoğu seçildiyse ekle
            if(!empty($init_depo) && $init_stok > 0){
                $baglanti->query("INSERT INTO stok(urun_id,depo_id,miktar) VALUES('$yeni_id','$init_depo','$init_stok')
                  ON DUPLICATE KEY UPDATE miktar=miktar+$init_stok");
                $baglanti->query("INSERT INTO hareketler(urun_id,depo_id,islem,miktar) VALUES('$yeni_id','$init_depo','stok_giris','$init_stok')");
            }
            $mesaj=$T['success_product']; $mesaj_tip='success';
        }
        else { $mesaj=$T['err_product'].$stmt->error; $mesaj_tip='danger'; }
        $stmt->close();
    }
}
?>

<div style="margin-bottom:28px">
  <div class="page-heading"><?= $T['add_product'] ?> <span><?= $T['add_product_h'] ?></span></div>
  <div class="page-subheading"><?= $T['add_product_sub'] ?></div>
</div>

<?php if($mesaj): ?>
<div class="alert alert-<?= $mesaj_tip ?>" style="margin-bottom:24px">
  <i class="bi bi-<?= $mesaj_tip=='success'?'check-circle':'exclamation-circle' ?>"></i> <?= $mesaj ?>
</div>
<?php endif; ?>

<div class="card" style="max-width:520px;padding:32px">
  <form method="post">
    <div style="margin-bottom:20px">
      <label class="form-label"><?= $T['lbl_barcode'] ?></label>
      <input class="form-control" name="barkod" placeholder="<?= $T['ph_barcode'] ?>" required>
    </div>
    <div style="margin-bottom:20px">
      <label class="form-label"><?= $T['lbl_product_name'] ?></label>
      <input class="form-control" name="urun_adi" placeholder="<?= $T['ph_product_name'] ?>" required>
    </div>
    <div style="margin-bottom:28px">
      <label class="form-label"><?= $T['lbl_desc'] ?> <span style="color:var(--text-muted);font-size:11px;text-transform:none;letter-spacing:0"><?= $T['lbl_optional'] ?></span></label>
      <textarea class="form-control" name="aciklama" placeholder="<?= $T['ph_desc'] ?>" rows="3" style="resize:vertical"></textarea>
    </div>

    <!-- Başlangıç deposu & stok -->
    <div style="background:var(--bg-secondary);border:1px solid var(--border);border-radius:10px;padding:18px 20px;margin-bottom:28px">
      <div style="font-size:11px;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:14px;font-family:'Space Mono',monospace">
        <i class="bi bi-box-arrow-in-down" style="margin-right:6px;color:var(--accent)"></i>
        <?= $T['lbl_init_depot'] ?> <span style="font-size:10px;font-weight:400;text-transform:none;letter-spacing:0"><?= $T['lbl_init_optional'] ?></span>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
        <div>
          <label class="form-label"><?= $T['lbl_init_depot'] ?></label>
          <select class="form-control form-select" name="init_depo">
            <option value="">— <?= $T['lbl_init_optional'] ?> —</option>
            <?php $dp=$baglanti->query("SELECT * FROM depolar"); while($x=$dp->fetch_assoc()): ?>
            <option value="<?= $x['id'] ?>"><?= htmlspecialchars($x['depo_adi']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label class="form-label"><?= $T['lbl_init_stock'] ?></label>
          <input class="form-control" name="init_stok" type="number" placeholder="0" min="0">
        </div>
      </div>
    </div>

    <div style="display:flex;gap:12px;align-items:center">
      <button type="submit" class="btn-accent"><i class="bi bi-plus-lg"></i> <?= $T['btn_save'] ?></button>
      <a href="urunler.php" style="font-size:13px;color:var(--text-muted);text-decoration:none"><?= $T['back_to_list'] ?></a>
    </div>
  </form>
</div>

<?php include "footer.php"; ?>
