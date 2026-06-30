<?php

use App\Core\Env;
use App\Core\Session;

$appName = Env::get('APP_NAME', 'RT Voting');
$error = Session::flash('error');
$success = Session::flash('success');

?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? $appName) ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link 
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" 
        rel="stylesheet"
    >

    <style>
    :root {
        --primary-color: #0d6efd;
    }
    body, html {
        height: 100%;
        margin: 0;
        background-color: #fff !important;
    }
    .login-container {
        display: flex;
        height: 100vh;
        width: 100%;
    }
    /* Sisi Kiri: Gambar Background */
    .login-image {
        flex: 1.2;
        background:  
                    url('<?php echo BASE_URL; ?>/assets/images/logo_pilkadart11.png');
        
        /* KUNCI RESPONSIVE: */
        background-size: contain;      /* Memastikan gambar menutupi seluruh area tanpa gepeng */
        background-position: center;  /* Gambar tetap di tengah meskipun layar menyempit */
        background-repeat: no-repeat; /* Mencegah gambar berulang jika layar terlalu besar */
        
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 50px;
        color: white;
        transition: all 0.3s ease;    /* Opsional: agar transisi smooth saat resize window */
    }
    /* Sisi Kanan: Form Login */
    .login-form-section {
        flex: 0.8;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 40px;
        background: #fff;
    }
    .login-form-box {
        width: 100%;
        max-width: 400px;
    }
    .brand-text {
        color: var(--primary-color);
        font-weight: 600;
        font-size: 1rem;
        margin-bottom: 5px;
    }
    .btn-success {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        padding: 10px;
        font-weight: 600;
    }
    .form-control {
        height: 40px;
        border-radius: 8px;
    }
    .input-group-text {
        border-radius: 0 8px 8px 0;
    }
    
    /* Responsive untuk HP */
    @media (max-width: 768px) {
        .login-image { display: none; }
        .login-form-section { flex: 1; }
    }
  </style>
</head>
<body class="bg-light">

<div class="login-container">
    <div class="login-image">
        <!-- <h2 class="font-weight-bold">SIM As Salam</h2>
        <p>Solusi Terintegrasi Manajemen Pendidikan Madrasah.</p> -->
    </div>
    <div class="login-form-section">
        <div class="login-form-box">
            <div class="text-center mb-4">
                <div class="brand-text">Selamat Datang di e-PILKADA</div>
                <p class="text-muted"><?= htmlspecialchars($appName) ?></p>
            </div>

            <div>
                <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                <?php endif; ?>

                <?= $content ?>
            </div>

            <div class="text-center mt-2">
                <p class="small text-muted">&copy; 2026 JUMPQ Innovations, PT. Khalifa Andara Solusindo Group</p>
            </div>
        </div>
    </div>
</div>

</body>
</html>