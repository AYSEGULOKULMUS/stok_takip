<?php
include "db.php";
session_start();

// Dil değiştirme (login sayfası için ayrı)
if(isset($_GET['lang'])){
    $_SESSION['lang'] = $_GET['lang'];
    header("Location: login.php");
    exit();
}
include "lang.php";

if($_POST){
    $kullanici = $_POST['username'];
    $sifre     = $_POST['password'];
    $sorgu = $baglanti->query("SELECT * FROM users WHERE username='$kullanici' AND password='$sifre'");
    if($sorgu->num_rows > 0){
        $_SESSION['login']    = true;
        $_SESSION['username'] = $kullanici;
        $_SESSION['role']     = ($kullanici=="admin") ? "admin" : "personel";
        header("Location:index.php");
        exit();
    } else {
        $hata = $T['err_login'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Stok Sistemi — <?= $T['btn_login'] ?></title>
<link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.css">
<style>
  *{box-sizing:border-box;margin:0;padding:0;}
  body{min-height:100vh;background:#0f1117;font-family:'DM Sans',sans-serif;display:flex;align-items:center;justify-content:center;position:relative;overflow:hidden;}
  body::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(249,115,22,0.04) 1px,transparent 1px),linear-gradient(90deg,rgba(249,115,22,0.04) 1px,transparent 1px);background-size:40px 40px;pointer-events:none;}
  .blob{position:absolute;border-radius:50%;filter:blur(100px);pointer-events:none;opacity:.3;}
  .blob-1{width:400px;height:400px;background:#f97316;top:-100px;right:-100px;}
  .blob-2{width:300px;height:300px;background:#38bdf8;bottom:-80px;left:-80px;}
  .login-box{position:relative;width:420px;background:#161b27;border:1px solid rgba(255,255,255,0.08);border-radius:20px;padding:48px 40px;box-shadow:0 30px 80px rgba(0,0,0,0.5);z-index:1;}
  .logo-area{text-align:center;margin-bottom:36px;}
  .logo-icon{display:inline-flex;align-items:center;justify-content:center;width:64px;height:64px;background:linear-gradient(135deg,#f97316,#ea580c);border-radius:18px;font-size:28px;color:#fff;margin-bottom:16px;box-shadow:0 8px 24px rgba(249,115,22,0.4);}
  .logo-title{font-family:'Space Mono',monospace;font-size:18px;font-weight:700;color:#e8eaf0;margin-bottom:6px;}
  .logo-sub{font-size:13px;color:#7a8399;}
  .field-group{margin-bottom:18px;}
  .field-label{display:block;font-size:11px;font-weight:700;color:#7a8399;text-transform:uppercase;letter-spacing:1px;margin-bottom:8px;font-family:'Space Mono',monospace;}
  .field-wrap{position:relative;}
  .field-icon{position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#7a8399;font-size:16px;}
  .field-input{width:100%;background:#0f1117;border:1px solid rgba(255,255,255,0.08);border-radius:10px;color:#e8eaf0;font-size:14px;padding:12px 14px 12px 42px;font-family:'DM Sans',sans-serif;transition:all .15s;outline:none;}
  .field-input::placeholder{color:#4a5568;}
  .field-input:focus{border-color:#f97316;box-shadow:0 0 0 3px rgba(249,115,22,0.15);}
  .error-box{background:rgba(244,63,94,0.1);border:1px solid rgba(244,63,94,0.25);border-radius:10px;color:#f43f5e;padding:12px 16px;font-size:13px;margin-bottom:18px;display:flex;align-items:center;gap:8px;}
  .btn-login{width:100%;background:linear-gradient(135deg,#f97316,#ea580c);border:none;border-radius:10px;color:#fff;font-size:15px;font-weight:600;padding:13px;cursor:pointer;font-family:'DM Sans',sans-serif;margin-top:8px;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;box-shadow:0 4px 15px rgba(249,115,22,0.3);}
  .btn-login:hover{transform:translateY(-1px);box-shadow:0 8px 25px rgba(249,115,22,0.45);}
  .footer-note{text-align:center;font-size:12px;color:#4a5568;margin-top:28px;font-family:'Space Mono',monospace;}
  .lang-row{display:flex;justify-content:center;gap:8px;margin-top:16px;}
  .lang-pill{padding:4px 12px;border-radius:20px;font-size:11px;font-weight:700;text-decoration:none;font-family:'Space Mono',monospace;color:#7a8399;background:rgba(255,255,255,0.05);transition:all .15s;}
  .lang-pill:hover{color:#e8eaf0;background:rgba(255,255,255,0.1);}
  .lang-pill.active{background:#f97316;color:#fff;}
</style>
</head>
<body>
<div class="blob blob-1"></div>
<div class="blob blob-2"></div>
<div class="login-box">
  <div class="logo-area">
    <div class="logo-icon"><i class="bi bi-box-seam-fill"></i></div>
    <div class="logo-title">STOK TAKİP</div>
    <div class="logo-sub"><?= $T['login_title'] ?></div>
  </div>

  <?php if(isset($hata)): ?>
  <div class="error-box"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($hata) ?></div>
  <?php endif; ?>

  <form method="post">
    <div class="field-group">
      <label class="field-label"><?= $T['lbl_username'] ?></label>
      <div class="field-wrap">
        <i class="bi bi-person field-icon"></i>
        <input class="field-input" type="text" name="username" placeholder="<?= $T['ph_username'] ?>" required autocomplete="username">
      </div>
    </div>
    <div class="field-group">
      <label class="field-label"><?= $T['lbl_password'] ?></label>
      <div class="field-wrap">
        <i class="bi bi-lock field-icon"></i>
        <input class="field-input" type="password" name="password" placeholder="••••••••" required autocomplete="current-password">
      </div>
    </div>
    <button class="btn-login" type="submit">
      <i class="bi bi-box-arrow-in-right"></i> <?= $T['btn_login'] ?>
    </button>
  </form>

  <div class="lang-row">
    <?php foreach($lang_labels as $code=>$label): ?>
    <a href="?lang=<?= $code ?>" class="lang-pill <?= $lang==$code?'active':'' ?>"><?= $label ?></a>
    <?php endforeach; ?>
  </div>

  <div class="footer-note">Stok Takip Sistemi © 2026</div>
</div>
</body>
</html>
