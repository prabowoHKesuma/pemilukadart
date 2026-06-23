<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Detail Verifikasi Remote</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= F::dash($election['title'] ?? null) ?></strong>
        </div>
    </div>

    <a href="<?= F::e($appUrl . $request['back_url']) ?>" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status</div>
                <div>
                    <span class="badge bg-<?= F::e($request['detail_status_badge'] ?? 'secondary') ?>">
                        <?= F::dash($request['detail_status_text'] ?? null) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Kode Verifikasi</div>
                <div class="h3 mb-0">
                    <?= F::dash($request['verification_code'] ?? null) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Expired Request</div>
                <div class="h6 mb-0">
                    <?= F::dateTime($request['expires_at'] ?? null) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning">
    Instruksi untuk pemilih:
    foto selfie harus memegang KTP atau kertas bertuliskan kode
    <strong><?= F::dash($request['verification_code'] ?? null) ?></strong>.
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Data Pemilih</strong>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <div class="text-muted small">Kode Pemilih</div>
                <strong><?= F::dash($request['voter_code'] ?? null) ?></strong>
            </div>

            <div class="col-md-3 mb-2">
                <div class="text-muted small">Nama</div>
                <strong><?= F::dash($request['voter_name'] ?? null) ?></strong>
            </div>

            <div class="col-md-3 mb-2">
                <div class="text-muted small">RT/RW</div>
                <strong><?= F::dash($request['rt_rw_label'] ?? null) ?></strong>
            </div>

            <div class="col-md-3 mb-2">
                <div class="text-muted small">HP</div>
                <strong><?= F::dash($request['phone'] ?? null) ?></strong>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($request['can_upload_photos'])): ?>
    <div class="card mb-3">
        <div class="card-header">
            <strong>Upload Foto Verifikasi</strong>
        </div>

        <div class="card-body">
            <form
                method="post"
                action="<?= F::e($appUrl . $request['upload_url']) ?>"
                enctype="multipart/form-data"
            >
                <?= Csrf::field() ?>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Foto KTP</label>
                        <input
                            type="file"
                            name="ktp_photo"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            required
                        >
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Foto Selfie + Kode Verifikasi</label>
                        <input
                            type="file"
                            name="selfie_photo"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            required
                        >
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
                        Pemilih menyetujui penggunaan foto KTP dan selfie hanya untuk verifikasi pemilihan RT ini.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary">
                    Upload Foto
                </button>
            </form>
        </div>
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <strong>Foto KTP</strong>
            </div>

            <div class="card-body text-center">
                <?php if (!empty($request['ktp_file_url'])): ?>
                    <img
                        src="<?= F::e($appUrl . $request['ktp_file_url']) ?>"
                        class="img-fluid rounded border"
                        style="max-height: 420px;"
                        alt="Foto KTP"
                    >
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        Belum diupload.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <strong>Foto Selfie</strong>
            </div>

            <div class="card-body text-center">
                <?php if (!empty($request['selfie_file_url'])): ?>
                    <img
                        src="<?= F::e($appUrl . $request['selfie_file_url']) ?>"
                        class="img-fluid rounded border"
                        style="max-height: 420px;"
                        alt="Foto Selfie"
                    >
                <?php else: ?>
                    <div class="alert alert-secondary mb-0">
                        Belum diupload.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Approval</strong>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="text-muted small">Approval 1</div>
                <strong><?= F::dash($request['verifier_1_name'] ?? null) ?></strong>
            </div>

            <div class="col-md-6">
                <div class="text-muted small">Approval 2</div>
                <strong><?= F::dash($request['verifier_2_name'] ?? null) ?></strong>
            </div>
        </div>

        <?php if (($request['status'] ?? '') === 'rejected'): ?>
            <div class="alert alert-danger">
                <strong>Alasan ditolak:</strong>
                <?= F::nl2brSafe($request['reject_reason'] ?? null) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($request['can_approve'])): ?>
            <form
                method="post"
                action="<?= F::e($appUrl . $request['approve_url']) ?>"
                class="d-inline"
                onsubmit="return confirm('Yakin approve verifikasi remote ini?');"
            >
                <?= Csrf::field() ?>

                <button type="submit" class="btn btn-success">
                    Approve
                </button>
            </form>
        <?php endif; ?>

        <?php if (!empty($request['can_reject'])): ?>
            <hr>

            <form
                method="post"
                action="<?= F::e($appUrl . $request['reject_url']) ?>"
                onsubmit="return confirm('Yakin tolak verifikasi remote ini?');"
            >
                <?= Csrf::field() ?>

                <div class="mb-2">
                    <label class="form-label">Alasan Penolakan</label>
                    <textarea
                        name="reject_reason"
                        class="form-control"
                        rows="3"
                        placeholder="Contoh: foto KTP tidak jelas / selfie tidak memegang kode"
                    ></textarea>
                </div>

                <button type="submit" class="btn btn-danger">
                    Reject
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>