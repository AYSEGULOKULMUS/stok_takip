<?php
if(session_status()===PHP_SESSION_NONE) session_start();

if(!isset($_SESSION['login'])){
    header("Location:login.php");
    exit();
}

include "lang.php";

$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stok Takip Sistemi</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<style>
  :root{--bg-primary:#0f1117;--bg-secondary:#161b27;--bg-card:#1c2333;--bg-hover:#222c42;--accent:#f97316;--accent-dim:rgba(249,115,22,0.15);--accent-glow:rgba(249,115,22,0.4);--text-primary:#e8eaf0;--text-muted:#7a8399;--border:rgba(255,255,255,0.07);--success:#22c55e;--info:#38bdf8;--warning:#facc15;--danger:#f43f5e;--sidebar-width:240px;}
  body.light-theme{--bg-primary:#f0f2f5;--bg-secondary:#ffffff;--bg-card:#ffffff;--bg-hover:#f5f6fa;--text-primary:#1a1d2e;--text-muted:#6b7280;--border:rgba(0,0,0,0.1);}
  body.light-theme .sidebar{box-shadow:2px 0 12px rgba(0,0,0,0.08);}
  body.light-theme .top-bar{background:rgba(240,242,245,0.9);}
  body.light-theme .table thead th{background:#f8f9fb;}
  body.light-theme .form-control,body.light-theme .form-select{background:#f8f9fb;color:var(--text-primary);}
  body.light-theme .form-control:focus,body.light-theme .form-select:focus{background:#fff;}
  body.light-theme .card{box-shadow:0 2px 12px rgba(0,0,0,0.06);}
  .theme-toggle{display:flex;align-items:center;justify-content:space-between;padding:8px 12px;background:var(--bg-card);border-radius:8px;margin-bottom:8px;cursor:pointer;border:1px solid var(--border);transition:all .15s;}
  .theme-toggle:hover{border-color:var(--accent);}
  .theme-toggle span{font-size:12px;color:var(--text-muted);font-weight:500;}
  .toggle-switch{width:36px;height:20px;background:var(--bg-hover);border-radius:10px;position:relative;transition:background .2s;flex-shrink:0;}
  .toggle-switch.on{background:var(--accent);}
  .toggle-switch::after{content:'';position:absolute;width:14px;height:14px;background:#fff;border-radius:50%;top:3px;left:3px;transition:left .2s;box-shadow:0 1px 3px rgba(0,0,0,0.3);}
  .toggle-switch.on::after{left:19px;}
  *{box-sizing:border-box;margin:0;padding:0;}
  html,body{height:100%;background:var(--bg-primary);color:var(--text-primary);font-family:'DM Sans',sans-serif;}
  .sidebar{position:fixed;top:0;left:0;width:var(--sidebar-width);height:100vh;background:var(--bg-secondary);border-right:1px solid var(--border);display:flex;flex-direction:column;z-index:100;overflow:hidden;}
  .sidebar-logo{padding:28px 24px 20px;border-bottom:1px solid var(--border);}
  .sidebar-logo .brand{display:flex;align-items:center;gap:10px;text-decoration:none;}
  .sidebar-logo .brand-icon{width:36px;height:36px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;color:#fff;flex-shrink:0;}
  .sidebar-logo .brand-name{font-family:'Space Mono',monospace;font-size:13px;font-weight:700;color:var(--text-primary);line-height:1.2;}
  .sidebar-logo .brand-sub{font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;}
  .sidebar-nav{flex:1;padding:16px 12px;overflow-y:auto;}
  .sidebar-section{font-size:10px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:1.2px;padding:8px 12px 6px;margin-top:8px;}
  .nav-item{display:flex;align-items:center;gap:10px;padding:10px 12px;border-radius:8px;color:var(--text-muted);text-decoration:none;font-size:14px;font-weight:500;transition:all .15s;margin-bottom:2px;}
  .nav-item:hover{background:var(--bg-hover);color:var(--text-primary);}
  .nav-item.active{background:var(--accent-dim);color:var(--accent);border-left:2px solid var(--accent);}
  .nav-item i{font-size:16px;width:20px;text-align:center;}
  .sidebar-footer{padding:16px 12px;border-top:1px solid var(--border);}
  .lang-switcher{display:flex;gap:4px;margin-bottom:10px;background:var(--bg-card);border-radius:8px;padding:4px;}
  .lang-btn{flex:1;text-align:center;padding:6px 4px;border-radius:6px;font-size:11px;font-weight:700;color:var(--text-muted);text-decoration:none;transition:all .15s;font-family:'Space Mono',monospace;}
  .lang-btn:hover{color:var(--text-primary);background:var(--bg-hover);}
  .lang-btn.active{background:var(--accent);color:#fff;}
  .user-card{display:flex;align-items:center;gap:10px;padding:10px 12px;background:var(--bg-card);border-radius:10px;margin-bottom:8px;}
  .user-avatar{width:32px;height:32px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:14px;color:#fff;font-weight:700;flex-shrink:0;font-family:'Space Mono',monospace;}
  .user-name{font-size:13px;font-weight:600;color:var(--text-primary);}
  .user-role{font-size:11px;color:var(--text-muted);}
  .btn-logout{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:9px;background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.2);border-radius:8px;color:var(--danger);font-size:13px;font-weight:500;text-decoration:none;transition:all .15s;}
  .btn-logout:hover{background:rgba(244,63,94,0.2);color:var(--danger);}
  .main-wrapper{margin-left:var(--sidebar-width);min-height:100vh;display:flex;flex-direction:column;}
  .top-bar{position:sticky;top:0;background:rgba(15,17,23,0.85);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:14px 32px;display:flex;align-items:center;justify-content:space-between;z-index:50;}
  .top-bar .page-title{font-family:'Space Mono',monospace;font-size:15px;font-weight:700;color:var(--text-primary);}
  .top-bar .time-badge{font-size:12px;color:var(--text-muted);font-family:'Space Mono',monospace;background:var(--bg-card);padding:5px 12px;border-radius:20px;border:1px solid var(--border);}
  .page-content{flex:1;padding:32px;}
  .card{background:var(--bg-card);border:1px solid var(--border);border-radius:12px;}
  .page-heading{font-family:'Space Mono',monospace;font-size:20px;font-weight:700;color:var(--text-primary);margin-bottom:4px;}
  .page-heading span{color:var(--accent);}
  .page-subheading{font-size:13px;color:var(--text-muted);}
  .table{color:var(--text-primary);border-color:var(--border);margin:0;}
  .table thead th{background:var(--bg-secondary);color:var(--text-muted);font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:1px;border-bottom:1px solid var(--border);padding:12px 16px;font-family:'Space Mono',monospace;}
  .table tbody td{border-bottom:1px solid var(--border);padding:13px 16px;font-size:14px;vertical-align:middle;color:var(--text-primary);}
  .table tbody tr{transition:background .1s;}
  .table tbody tr:hover td{background:var(--bg-hover);}
  .table tbody tr:last-child td{border-bottom:none;}
  .table-wrapper{border-radius:12px;overflow:hidden;border:1px solid var(--border);}
  .form-label{font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.8px;margin-bottom:6px;font-family:'Space Mono',monospace;}
  .form-control,.form-select{background:var(--bg-secondary);border:1px solid var(--border);border-radius:8px;color:var(--text-primary);padding:10px 14px;font-size:14px;transition:all .15s;}
  .form-control:focus,.form-select:focus{background:var(--bg-secondary);border-color:var(--accent);box-shadow:0 0 0 3px var(--accent-dim);color:var(--text-primary);outline:none;}
  .form-control::placeholder{color:var(--text-muted);}
  .form-select option{background:var(--bg-secondary);color:var(--text-primary);}
  .btn-accent{background:var(--accent);color:#fff;border:none;padding:10px 22px;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:7px;}
  .btn-accent:hover{background:#ea6c0a;transform:translateY(-1px);box-shadow:0 4px 15px var(--accent-glow);}
  .btn-accent:active{transform:translateY(0);}
  .alert{border-radius:10px;border:none;padding:14px 18px;font-size:14px;display:flex;align-items:center;gap:10px;}
  .alert-success{background:rgba(34,197,94,0.12);color:var(--success);border-left:3px solid var(--success);}
  .alert-danger{background:rgba(244,63,94,0.12);color:var(--danger);border-left:3px solid var(--danger);}
  .alert-info{background:rgba(56,189,248,0.12);color:var(--info);border-left:3px solid var(--info);}
  .badge-giris{background:rgba(34,197,94,0.15);color:var(--success);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;font-family:'Space Mono',monospace;}
  .badge-transfer{background:rgba(56,189,248,0.15);color:var(--info);padding:4px 10px;border-radius:20px;font-size:12px;font-weight:600;font-family:'Space Mono',monospace;}
  ::-webkit-scrollbar{width:6px;height:6px;}
  ::-webkit-scrollbar-track{background:transparent;}
  ::-webkit-scrollbar-thumb{background:var(--border);border-radius:3px;}
  ::-webkit-scrollbar-thumb:hover{background:var(--text-muted);}
</style>
</head>
<body id="main-body">
<script>
if(localStorage.getItem('stok_theme')==='light') document.getElementById('main-body').classList.add('light-theme');
</script>

<div class="sidebar">
  <div class="sidebar-logo">
    <a href="index.php" class="brand">
      <div class="brand-icon"><i class="bi bi-box-seam-fill"></i></div>
      <div>
        <div class="brand-name">STOK SİSTEM</div>
        <div class="brand-sub"><?= $T['brand_sub'] ?></div>
      </div>
    </a>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-section"><?= $T['nav_general'] ?></div>
    <a href="index.php" class="nav-item <?= $current_page=='index.php'?'active':'' ?>">
      <i class="bi bi-grid-1x2"></i> <?= $T['nav_dashboard'] ?>
    </a>

    <div class="sidebar-section"><?= $T['nav_products'] ?></div>
    <a href="urunler.php" class="nav-item <?= $current_page=='urunler.php'?'active':'' ?>">
      <i class="bi bi-boxes"></i> <?= $T['nav_list'] ?>
    </a>
    <a href="urun_ekle.php" class="nav-item <?= $current_page=='urun_ekle.php'?'active':'' ?>">
      <i class="bi bi-plus-square"></i> <?= $T['nav_add'] ?>
    </a>

    <div class="sidebar-section"><?= $T['nav_stock'] ?></div>
    <a href="stok_giris.php" class="nav-item <?= $current_page=='stok_giris.php'?'active':'' ?>">
      <i class="bi bi-box-arrow-in-down"></i> <?= $T['nav_entry'] ?>
    </a>
    <a href="transfer.php" class="nav-item <?= $current_page=='transfer.php'?'active':'' ?>">
      <i class="bi bi-arrow-left-right"></i> <?= $T['nav_transfer'] ?>
    </a>
    <a href="hareketler.php" class="nav-item <?= $current_page=='hareketler.php'?'active':'' ?>">
      <i class="bi bi-clock-history"></i> <?= $T['nav_movements'] ?>
    </a>
    <a href="sayim.php" class="nav-item <?= $current_page=='sayim.php'?'active':'' ?>">
      <i class="bi bi-clipboard2-check"></i> <?= $T['nav_count'] ?>
    </a>

    <div class="sidebar-section"><?= $T['nav_orders'] ?></div>
    <a href="siparisler.php" class="nav-item <?= in_array($current_page,['siparisler.php','siparis_detay.php'])?'active':'' ?>">
      <i class="bi bi-cart3"></i> <?= $T['nav_order_list'] ?>
    </a>
    <a href="siparis_ekle.php" class="nav-item <?= $current_page=='siparis_ekle.php'?'active':'' ?>">
      <i class="bi bi-cart-plus"></i> <?= $T['nav_order_add'] ?>
    </a>
  </nav>

  <div class="sidebar-footer">
    <div class="lang-switcher">
      <?php foreach($lang_labels as $code => $label): ?>
        <a href="?lang=<?= $code ?>" class="lang-btn <?= $lang==$code?'active':'' ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </div>
    <div class="theme-toggle" onclick="toggleTheme()" id="theme-btn">
      <span id="theme-label"><i class="bi bi-sun"></i> <?= $T['theme_light'] ?></span>
      <div class="toggle-switch" id="theme-switch"></div>
    </div>
    <div class="user-card">
      <div class="user-avatar"><?= strtoupper(substr($_SESSION['username']??'U',0,1)) ?></div>
      <div>
        <div class="user-name"><?= htmlspecialchars($_SESSION['username']??'') ?></div>
        <div class="user-role"><?= ucfirst($_SESSION['role']??'personel') ?></div>
      </div>
    </div>
    <a href="logout.php" class="btn-logout">
      <i class="bi bi-box-arrow-right"></i> <?= $T['logout'] ?>
    </a>
  </div>
</div>

<div class="main-wrapper">
  <div class="top-bar">
    <div class="page-title"><?= $T['pages'][$current_page] ?? 'Stok Sistemi' ?></div>
    <div class="time-badge" id="clock"></div>
  </div>
  <div class="page-content">
<script>
(function(){
  const localeMap={tr:'tr-TR',en:'en-GB',de:'de-DE'};
  const locale=localeMap['<?= $lang ?>']||'tr-TR';
  function tick(){
    const n=new Date();
    document.getElementById('clock').textContent=
      n.toLocaleDateString(locale,{day:'2-digit',month:'short',year:'numeric'})+' · '+
      n.toLocaleTimeString(locale,{hour:'2-digit',minute:'2-digit'});
  }
  tick(); setInterval(tick,1000);
})();

const darkLabel  = '<?= addslashes($T['theme_dark']) ?>';
const lightLabel = '<?= addslashes($T['theme_light']) ?>';
function applyTheme(isDark){
  const body = document.getElementById('main-body');
  const sw   = document.getElementById('theme-switch');
  const lbl  = document.getElementById('theme-label');
  if(isDark){
    body.classList.remove('light-theme');
    sw.classList.remove('on');
    lbl.innerHTML = '<i class="bi bi-moon"></i> '+darkLabel;
  } else {
    body.classList.add('light-theme');
    sw.classList.add('on');
    lbl.innerHTML = '<i class="bi bi-sun"></i> '+lightLabel;
  }
}
function toggleTheme(){
  const isLight = document.getElementById('main-body').classList.contains('light-theme');
  localStorage.setItem('stok_theme', isLight ? 'dark' : 'light');
  applyTheme(isLight);
}
(function(){
  const saved = localStorage.getItem('stok_theme');
  applyTheme(saved !== 'light');
})();
</script>
