<?php include "header.php"; ?>

<div style="margin-bottom:32px">
  <div class="page-heading"><?= $T['welcome'] ?> <span><?= htmlspecialchars($_SESSION['username']??'') ?></span></div>
  <div class="page-subheading"><?= $T['dashboard_sub'] ?></div>
</div>

<div class="row g-3 mb-3">
  <div class="col-md-4">
    <a href="urun_ekle.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='var(--accent)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:var(--accent-dim);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-plus-square-fill" style="font-size:20px;color:var(--accent)"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['card_add_product'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['card_add_sub'] ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="stok_giris.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='var(--success)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:rgba(34,197,94,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-box-arrow-in-down" style="font-size:20px;color:var(--success)"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['card_stock_entry'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['card_stock_sub'] ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-4">
    <a href="transfer.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='var(--info)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:rgba(56,189,248,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-arrow-left-right" style="font-size:20px;color:var(--info)"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['card_transfer'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['card_transfer_sub'] ?></div>
      </div>
    </a>
  </div>
</div>

<div class="row g-3">
  <div class="col-md-6">
    <a href="urunler.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='var(--warning)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:rgba(250,204,21,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-boxes" style="font-size:20px;color:var(--warning)"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['card_list'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['card_list_sub'] ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-6">
    <a href="hareketler.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='rgba(149,115,255,0.5)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:rgba(149,115,255,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-clock-history" style="font-size:20px;color:#9573ff"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['card_movements'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['card_movements_sub'] ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-6">
    <a href="sayim.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='rgba(56,189,248,0.5)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:rgba(56,189,248,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-clipboard2-check" style="font-size:20px;color:var(--info)"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['nav_count'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['count_sub'] ?></div>
      </div>
    </a>
  </div>
  <div class="col-md-6">
    <a href="siparisler.php" style="text-decoration:none">
      <div class="card" style="padding:24px;transition:all .2s;cursor:pointer"
           onmouseover="this.style.borderColor='rgba(249,115,22,0.5)';this.style.transform='translateY(-2px)'"
           onmouseout="this.style.borderColor='var(--border)';this.style.transform='translateY(0)'">
        <div style="width:44px;height:44px;background:var(--accent-dim);border-radius:10px;display:flex;align-items:center;justify-content:center;margin-bottom:16px">
          <i class="bi bi-cart3" style="font-size:20px;color:var(--accent)"></i>
        </div>
        <div style="font-weight:600;font-size:15px;margin-bottom:4px"><?= $T['card_orders'] ?></div>
        <div style="font-size:13px;color:var(--text-muted)"><?= $T['card_orders_sub'] ?></div>
      </div>
    </a>
  </div>
</div>

<?php include "footer.php"; ?>
