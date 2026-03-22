<?php
include "header.php";
include "db.php";

$sorgu = $baglanti->query("
  SELECT urunler.urun_adi, depolar.depo_adi, hareketler.islem,
         hareketler.miktar, hareketler.tarih
  FROM hareketler
  JOIN urunler ON hareketler.urun_id=urunler.id
  JOIN depolar ON hareketler.depo_id=depolar.id
  ORDER BY tarih DESC
");
?>

<div style="margin-bottom:28px">
  <div class="page-heading"><?= $T['movements_h1'] ?> <span><?= $T['movements_h2'] ?></span></div>
  <div class="page-subheading"><?= $T['movements_sub'] ?></div>
</div>

<div class="table-wrapper">
  <table class="table">
    <thead>
      <tr>
        <th><?= $T['col_product'] ?></th>
        <th><?= $T['col_warehouse'] ?></th>
        <th><?= $T['col_operation'] ?></th>
        <th><?= $T['col_quantity'] ?></th>
        <th><?= $T['col_date'] ?></th>
      </tr>
    </thead>
    <tbody>
      <?php while($row=$sorgu->fetch_assoc()): ?>
      <tr>
        <td style="font-weight:500"><?= htmlspecialchars($row['urun_adi']) ?></td>
        <td style="color:var(--text-muted);font-size:13px"><?= htmlspecialchars($row['depo_adi']) ?></td>
        <td>
          <?php if($row['islem']=='stok_giris'): ?>
            <span class="badge-giris"><i class="bi bi-arrow-down-circle"></i> <?= $T['badge_entry'] ?></span>
          <?php else: ?>
            <span class="badge-transfer"><i class="bi bi-arrow-left-right"></i> <?= $T['badge_transfer'] ?></span>
          <?php endif; ?>
        </td>
        <td>
          <span style="font-family:'Space Mono',monospace;font-weight:700;font-size:15px"><?= htmlspecialchars($row['miktar']) ?></span>
          <span style="font-size:12px;color:var(--text-muted)"> <?= $T['unit'] ?></span>
        </td>
        <td style="font-size:13px;color:var(--text-muted);font-family:'Space Mono',monospace"><?= htmlspecialchars($row['tarih']) ?></td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</div>

<?php include "footer.php"; ?>
