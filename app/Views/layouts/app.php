<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;
use App\Core\Session;

$appName = Env::get('APP_NAME', 'RT Voting');
$user = Auth::user();

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
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?= htmlspecialchars(Env::get('APP_URL')) ?>">
            <?= htmlspecialchars($appName) ?>
        </a>

        <div class="d-flex align-items-center gap-3">
            <span class="text-white small">
                <?= htmlspecialchars($user['name'] ?? '') ?> 
                <?php if (!empty($user['role'])): ?>
                    (<?= htmlspecialchars($user['role']) ?>)
                <?php endif; ?>
            </span>

            <form method="post" action="<?= htmlspecialchars(Env::get('APP_URL')) ?>/logout" class="mb-0">
                <?= Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">
                    Logout
                </button>
            </form>
        </div>
    </div>
</nav>

<div class="container-fluid">
    <div class="row">
        <aside class="col-md-2 bg-light min-vh-100 border-end pt-3">
            <div class="list-group list-group-flush">
                <a href="<?= htmlspecialchars(Env::get('APP_URL')) ?>" class="list-group-item list-group-item-action">
                    Dashboard
                </a>

                <a href="#" class="list-group-item list-group-item-action disabled">
                    Pemilihan
                </a>

                <a href="#" class="list-group-item list-group-item-action disabled">
                    Pemilih
                </a>

                <a href="#" class="list-group-item list-group-item-action disabled">
                    Kandidat
                </a>

                <a href="#" class="list-group-item list-group-item-action disabled">
                    Voting TPS
                </a>

                <a href="#" class="list-group-item list-group-item-action disabled">
                    Verifikasi Remote
                </a>

                <a href="#" class="list-group-item list-group-item-action disabled">
                    Hasil
                </a>
            </div>
        </aside>

        <main class="col-md-10 pt-4 px-4">
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>

            <?= $content ?>
        </main>
    </div>
</div>

</body>
</html>