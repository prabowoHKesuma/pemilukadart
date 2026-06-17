<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function detailStatusText(array $request): string
{
    if ($request['status'] === 'approved') {
        return 'APPROVED';
    }

    if ($request['status'] === 'rejected') {
        return 'REJECTED';
    }

    if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
        return 'MENUNGGU UPLOAD FOTO';
    }

    if (!empty($request['verified_by_1']) && empty($request['verified_by_2'])) {
        return 'MENUNGGU APPROVAL KEDUA';
    }

    return 'MENUNGGU APPROVAL PERTAMA';
}

function detailStatusBadge(array $request): string
{
    if ($request['status'] === 'approved') {
        return 'success';
    }

    if ($request['status'] === 'rejected') {
        return 'danger';
    }

    if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
        return 'secondary';
    }

    if (!empty($request['verified_by_1']) && empty($request['verified_by_2'])) {
        return 'warning';
    }

    return 'info';
}

$canProcess = $request['status'] === 'pending';
$hasPhotos = !empty($request['ktp_photo_path']) && !empty($request['selfie_photo_path']);

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Detail Verifikasi Remote</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status</div>
                <div>
                    <span class="badge bg-<?= detailStatusBadge($request) ?>">
                        <?= htmlspecialchars(detailStatusText($request)) ?>
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
                    <?= htmlspecialchars($request['verification_code']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Expired Request</div>
                <div class="h6 mb-0">
                    <?= $request['expires_at'] ? date('d/m/Y H:i', strtotime($request['expires_at'])) : '-' ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="alert alert-warning">
    Instruksi untuk pemilih:
    foto selfie harus memegang KTP atau kertas bertuliskan kode
    <strong><?= htmlspecialchars($request['verification_code']) ?></strong>.
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Data Pemilih</strong>
    </div>

    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <div class="text-muted small">Kode Pemilih</div>
                <strong><?= htmlspecialchars($request['voter_code']) ?></strong>
            </div>

            <div class="col-md-3 mb-2">
                <div class="text-muted small">Nama</div>
                <strong><?= htmlspecialchars($request['voter_name']) ?></strong>
            </div>

            <div class="col-md-3 mb-2">
                <div class="text-muted small">RT/RW</div>
                <strong><?= htmlspecialchars(($request['rt'] ?: '-') . ' / ' . ($request['rw'] ?: '-')) ?></strong>
            </div>

            <div class="col-md-3 mb-2">
                <div class="text-muted small">HP</div>
                <strong><?= htmlspecialchars($request['phone'] ?? '-') ?></strong>
            </div>
        </div>
    </div>
</div>

<?php if ($canProcess): ?>
    <div class="card mb-3">
        <div class="card-header">
            <strong>Upload Foto Verifikasi</strong>
        </div>

        <div class="card-body">
            <form 
                method="post" 
                action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications/<?= $request['id'] ?>/upload"
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
                <?php if (!empty($request['ktp_photo_path'])): ?>
                    <img 
                        src="<?= htmlspecialchars($appUrl) ?>/remote-verifications/<?= $request['id'] ?>/file/ktp"
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
                <?php if (!empty($request['selfie_photo_path'])): ?>
                    <img 
                        src="<?= htmlspecialchars($appUrl) ?>/remote-verifications/<?= $request['id'] ?>/file/selfie"
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
                <strong><?= htmlspecialchars($request['verifier_1_name'] ?? '-') ?></strong>
            </div>

            <div class="col-md-6">
                <div class="text-muted small">Approval 2</div>
                <strong><?= htmlspecialchars($request['verifier_2_name'] ?? '-') ?></strong>
            </div>
        </div>

        <?php if ($request['status'] === 'rejected'): ?>
            <div class="alert alert-danger">
                <strong>Alasan ditolak:</strong>
                <?= nl2br(htmlspecialchars($request['reject_reason'] ?? '-')) ?>
            </div>
        <?php endif; ?>

        <?php if ($canProcess && $hasPhotos): ?>
            <form 
                method="post" 
                action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications/<?= $request['id'] ?>/approve"
                class="d-inline"
                onsubmit="return confirm('Yakin approve verifikasi remote ini?');"
            >
                <?= Csrf::field() ?>

                <button type="submit" class="btn btn-success">
                    Approve
                </button>
            </form>
        <?php endif; ?>

        <?php if ($canProcess): ?>
            <hr>

            <form 
                method="post" 
                action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications/<?= $request['id'] ?>/reject"
                onsubmit="return confirm('Yakin tolak verifikasi remote ini?');"
            >
                <?= Csrf::field() ?>

                <div class="mb-2">
                    <label class="form-label">Alasan Penolakan</label>
                    <textarea name="reject_reason" class="form-control" rows="3" placeholder="Contoh: foto KTP tidak jelas / selfie tidak memegang kode"></textarea>
                </div>

                <button type="submit" class="btn btn-danger">
                    Reject
                </button>
            </form>
        <?php endif; ?>
    </div>
</div>