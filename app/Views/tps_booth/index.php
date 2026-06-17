<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4 text-center">
                <h1 class="h3 mb-2">Masukkan Kode Bilik</h1>

                <p class="text-muted mb-4">
                    Kode diberikan oleh panitia setelah data Anda divalidasi.
                </p>

                <form method="post" action="<?= htmlspecialchars($appUrl) ?>/tps-booth/check">
                    <?= Csrf::field() ?>

                    <input 
                        type="text" 
                        name="booth_code" 
                        class="form-control form-control-lg text-center mb-3"
                        placeholder="6 digit kode"
                        maxlength="6"
                        inputmode="numeric"
                        pattern="[0-9]{6}"
                        autocomplete="off"
                        autofocus
                        required
                        style="font-size: 2rem; letter-spacing: 8px; font-weight: bold;"
                    >

                    <button type="submit" class="btn btn-success btn-lg w-100">
                        Mulai Coblos
                    </button>
                </form>
            </div>
        </div>

        <div class="text-center text-muted small mt-3">
            Jika kode expired atau tidak valid, hubungi panitia di meja validasi.
        </div>
    </div>
</div>