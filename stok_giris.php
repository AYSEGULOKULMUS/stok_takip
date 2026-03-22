<?php
include "header.php";
include "db.php";

if($_POST){
    $urun=$_POST['urun']; $depo=$_POST['depo']; $miktar=$_POST['miktar'];
    $baglanti->query("INSERT INTO stok(urun_id,depo_id,miktar) VALUES('$urun','$depo','$miktar')");
    $baglanti->query("INSERT INTO hareketler(urun_id,depo_id,islem,miktar) VALUES('$urun','$depo','stok_giris','$miktar')");
    $basari = $T['success_stock'];
}
?>

<div style="margin-bottom:28px">
  <div class="page-heading"><?= $T['stock_entry'] ?> <span><?= $T['stock_entry_h'] ?></span></div>
  <div class="page-subheading"><?= $T['stock_entry_sub'] ?></div>
</div>

<?php if(isset($basari)): ?>
<div class="alert alert-success" style="margin-bottom:24px">
  <i class="bi bi-check-circle"></i> <?= $basari ?>
</div>
<?php endif; ?>

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
    <div style="margin-bottom:20px">
      <label class="form-label"><?= $T['lbl_warehouse'] ?></label>
      <select class="form-control form-select" name="depo">
        <?php $d=$baglanti->query("SELECT * FROM depolar"); while($x=$d->fetch_assoc()): ?>
        <option value="<?= $x['id'] ?>"><?= htmlspecialchars($x['depo_adi']) ?></option>
        <?php endwhile; ?>
      </select>
    </div>
    <div style="margin-bottom:28px">
      <label class="form-label"><?= $T['lbl_quantity'] ?></label>
      <input class="form-control" name="miktar" type="number" placeholder="0" min="1" required>
    </div>
    <div style="display:flex;gap:12px;align-items:center">
      <button type="submit" class="btn-accent" style="background:var(--success);box-shadow:0 4px 15px rgba(34,197,94,0.3)">
        <i class="bi bi-box-arrow-in-down"></i> <?= $T['btn_add_stock'] ?>
      </button>
      <a href="hareketler.php" style="font-size:13px;color:var(--text-muted);text-decoration:none"><?= $T['view_movements'] ?></a>
    </div>
  </form>
</div>

<?php include "footer.php"; ?>
