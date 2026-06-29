<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="container py-4">
    <div class="card mx-auto" style="max-width: 720px;">
        <div class="card-body">
            <h1 class="h4 mb-1">Upload Verifikasi Remote</h1>

            <div class="text-muted mb-3">
                Pemilihan: <strong><?= F::dash($request['election_title'] ?? null) ?></strong>
            </div>

            <div class="alert alert-warning">
                Selfie wajib memegang KTP atau kertas bertuliskan kode:
                <strong><?= F::dash($request['verification_code'] ?? null) ?></strong>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Nama Pemilih</div>
                <strong><?= F::dash($request['voter_name'] ?? null) ?></strong>
            </div>

            <div class="mb-3">
                <div class="text-muted small">Kode Pemilih</div>
                <strong><?= F::dash($request['voter_code'] ?? null) ?></strong>
            </div>

            <form method="post" enctype="multipart/form-data" action="">
                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label">Foto KTP</label>
                    <input
                        type="file"
                        name="ktp_photo"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Foto Selfie + Kode Verifikasi</label>
                    <input
                        type="file"
                        name="selfie_photo"
                        class="form-control"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        required
                    >
                    <div class="form-text">
                        Selfie harus jelas dan kode verifikasi harus terbaca.
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input
                        type="checkbox"
                        name="consent_accepted"
                        id="consent_accepted"
                        class="form-check-input"
                        required
                    >
                    <label class="form-check-label" for="consent_accepted">
                        Saya menyetujui penggunaan foto KTP dan selfie hanya untuk proses verifikasi pemilihan ini.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Upload Verifikasi
                </button>
            </form>
        </div>
    </div>
</div>