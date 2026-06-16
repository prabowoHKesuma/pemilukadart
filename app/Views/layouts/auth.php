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
</head>
<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center min-vh-100">
        <div class="col-md-5 col-lg-4">
            <div class="text-center mb-4">
                <h3><?= htmlspecialchars($appName) ?></h3>
                <p class="text-muted mb-0">PEMILU KADA RT</p>
            </div>

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
        </div>
    </div>
</div>

</body>
</html>