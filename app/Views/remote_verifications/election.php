<?php

use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Verifikasi Remote Pemilihan</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= F::dash($election['title'] ?? null) ?></strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= F::e($appUrl) ?>/remote-verifications" class="btn btn-secondary">
            Kembali
        </a>

        <?php if (!empty($canCreateRequest)): ?>
            <a
                href="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/remote-verifications/create"
                class="btn btn-primary"
            >
                + Buat Request
            </a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($isLocked)): ?>
    <div class="alert alert-warning">
        Pemilihan berstatus <strong><?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?></strong>.
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
                                <strong><?= F::dash($request['verification_code'] ?? null) ?></strong>
                            </td>

                            <td>
                                <strong><?= F::dash($request['voter_name'] ?? null) ?></strong>

                                <div class="small text-muted">
                                    <?= F::dash($request['voter_code'] ?? null) ?>
                                </div>

                                <div class="small text-muted">
                                    <?= F::dash($request['rt_rw_label'] ?? null) ?>
                                </div>
                            </td>

                            <td>
                                <?= F::e(F::channelLabel($request['allowed_channel'] ?? null)) ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e($request['status_badge'] ?? 'secondary') ?>">
                                    <?= F::dash($request['status_label'] ?? null) ?>
                                </span>

                                <?php if (($request['status'] ?? '') === 'rejected' && !empty($request['reject_reason'])): ?>
                                    <div class="small text-danger mt-1">
                                        <?= F::e($request['reject_reason']) ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= F::dash($request['verifier_1_name'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::dash($request['verifier_2_name'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::dateTime($request['created_at'] ?? null) ?>
                            </td>

                            <td>
                                <a
                                    href="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/remote-verifications/<?= F::e($request['id']) ?>"
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