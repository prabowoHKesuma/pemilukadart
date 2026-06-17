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
        body {
            background: #f5f6f8;
        }

        .tps-header {
            background: #111827;
            color: #fff;
            padding: 16px 0;
            margin-bottom: 24px;
        }

        .candidate-card {
            transition: 0.15s ease-in-out;
            border: 2px solid transparent;
        }

        .candidate-card:hover {
            transform: translateY(-2px);
            border-color: #198754;
        }

        .candidate-radio {
            width: 24px;
            height: 24px;
        }

        .candidate-name {
            font-size: 1.35rem;
            font-weight: 700;
        }

        .candidate-number {
            font-size: 1.2rem;
        }

        .btn-submit-vote {
            font-size: 1.4rem;
            padding: 14px 28px;
        }

        @media (max-width: 768px) {
            .candidate-name {
                font-size: 1.15rem;
            }

            .btn-submit-vote {
                width: 100%;
            }
        }
    </style>
</head>
<body>

<header class="tps-header">
    <div class="container">
        <div class="text-center">
            <h2 class="mb-0"><?= htmlspecialchars($appName) ?></h2>
            <div>Mode Coblos TPS</div>
        </div>
    </div>
</header>

<div class="container pb-5">
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

</body>
</html>