<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function remoteStatusLabel(array $request): string
{
    if ($request['status'] === 'approved') {
        return 'Approved';
    }

    if ($request['status'] === 'rejected') {
        return 'Rejected';
    }

    if (empty($request['ktp_photo_path']) || empty($request['selfie_photo_path'])) {
        return 'Menunggu Upload';
    }

    if (!empty($request['verified_by_1']) && empty($request['verified_by_2'])) {
        return 'Menunggu Approval 2';
    }

    return 'Menunggu Approval 1';
}

function remoteStatusBadge(array $request): string
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

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Verifikasi Remote Pemilihan</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars($appUrl) ?>/remote-verifications" class="btn btn-secondary">
            Kembali
        </a>

        <?php if (in_array($election['status'], ['draft', 'open'], true)): ?>
            <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications/create" class="btn btn-primary">
                + Buat Request
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!in_array($election['status'], ['draft', 'open'], true)): ?>
    <div class="alert alert-warning">
        Pemilihan berstatus <strong><?= htmlspecialchars(strtoupper($election['status'])) ?></strong>.
        Verifikasi remote sudah dikunci.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <div class="alert alert-info mb-0">
                Belum ada request verifikasi remote.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Verifikasi</th>
                        <th>Pemilih</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Approval 1</th>
                        <th>Approval 2</th>
                        <th>Dibuat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($requests as $index => $request): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($request['verification_code']) ?></strong>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($request['voter_name']) ?></strong>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($request['voter_code']) ?>
                                </div>
                                <div class="small text-muted">
                                    <?= htmlspecialchars(($request['rt'] ?: '-') . ' / ' . ($request['rw'] ?: '-')) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars($request['allowed_channel'] ?? '-') ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= remoteStatusBadge($request) ?>">
                                    <?= htmlspecialchars(remoteStatusLabel($request)) ?>
                                </span>

                                <?php if ($request['status'] === 'rejected' && !empty($request['reject_reason'])): ?>
                                    <div class="small text-danger mt-1">
                                        <?= htmlspecialchars($request['reject_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($request['verifier_1_name'] ?? '-') ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($request['verifier_2_name'] ?? '-') ?>
                            </td>

                            <td>
                                <?= $request['created_at'] ? date('d/m/Y H:i', strtotime($request['created_at'])) : '-' ?>
                            </td>

                            <td>
                                <a 
                                    href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications/<?= $request['id'] ?>" 
                                    class="btn btn-sm btn-primary"
                                >
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        <?php endif; ?>
    </div>
</div>