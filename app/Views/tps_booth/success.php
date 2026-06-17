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
                    Silakan keluar dari bilik. Komputer akan kembali ke halaman input kode.
                </p>

                <hr>

                <a href="<?= htmlspecialchars($appUrl) ?>/tps-booth" class="btn btn-primary btn-lg">
                    Kembali ke Input Kode
                </a>
            </div>
        </div>
    </div>
</div>

<script>
setTimeout(function () {
    window.location.href = "<?= htmlspecialchars($appUrl) ?>/tps-booth";
}, 5000);
</script>