<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card text-center shadow-sm">
            <div class="card-body py-5">
                <h1 class="text-success mb-3">Suara Berhasil Disimpan</h1>

                <p class="lead">
                    Terima kasih. Suara Anda sudah masuk ke sistem.
                </p>

                <p class="text-muted">
                    Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
                </p>

                <hr>

                <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/tps-voting" class="btn btn-primary">
                    Kembali ke Pencarian Pemilih
                </a>
            </div>
        </div>
    </div>
</div>