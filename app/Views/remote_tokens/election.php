<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Token Voting Remote</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= F::dash($election['title'] ?? null) ?></strong>
        </div>
    </div>

    <a href="<?= F::e($appUrl) ?>/remote-tokens" class="btn btn-secondary">
        Kembali
    </a>
</div>

<?php if (empty($isElectionOpen)): ?>
    <div class="alert alert-warning">
        Pemilihan belum berstatus <strong>OPEN</strong>.
        Token remote hanya bisa dibuat saat pemilihan sudah dibuka.
    </div>
<?php endif; ?>

<?php if (!empty($generatedLink)): ?>
    <div class="alert alert-success">
        <strong>Token berhasil dibuat.</strong>
        Copy link ini sekarang. Link tidak bisa ditampilkan lagi setelah halaman di-refresh.

        <div class="input-group mt-2">
            <input
                type="text"
                id="generated-token-link"
                class="form-control"
                value="<?= F::e($generatedLink) ?>"
                readonly
            >

            <button type="button" class="btn btn-dark" onclick="copyGeneratedLink()">
                Copy
            </button>
        </div>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">
        <strong>Remote Verification Approved</strong>
    </div>

    <div class="card-body">
        <?php if (empty($approvedRequests)): ?>
            <div class="alert alert-info mb-0">
                Belum ada verifikasi remote yang approved.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Kode Verifikasi</th>
                        <th>Pemilih</th>
                        <th>Channel</th>
                        <th>Status Coblos</th>
                        <th>Status Token</th>
                        <th>Expired Token</th>
                        <th style="width: 260px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($approvedRequests as $request): ?>
                        <tr>
                            <td>
                                <strong><?= F::dash($request['verification_code'] ?? null) ?></strong>
                            </td>

                            <td>
                                <strong><?= F::dash($request['voter_name'] ?? null) ?></strong>

                                <div class="small text-muted">
                                    <?= F::dash($request['voter_code'] ?? null) ?>
                                </div>

                                <div class="small text-muted">
                                    HP: <?= F::dash($request['phone'] ?? null) ?>
                                </div>
                            </td>

                            <td>
                                <?= F::e(F::channelLabel($request['allowed_channel'] ?? null)) ?>
                            </td>

                            <td>
                                <?php if (!empty($request['has_already_voted'])): ?>
                                    <span class="badge bg-success">Sudah Coblos</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e($request['token_status_badge'] ?? 'secondary') ?>">
                                    <?= F::dash($request['token_status_text'] ?? null) ?>
                                </span>
                            </td>

                            <td>
                                <?= F::dateTime($request['token_expires_at'] ?? null) ?>
                            </td>

                            <td>
                                <?php if (!empty($request['can_generate_token'])): ?>
                                    <form
                                        method="post"
                                        action="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/remote-tokens/<?= F::e($request['id']) ?>/generate"
                                        class="d-flex gap-1"
                                        onsubmit="return confirm('Generate token remote untuk pemilih ini?');"
                                    >
                                        <?= Csrf::field() ?>

                                        <select name="expires_minutes" class="form-select form-select-sm">
                                            <?php foreach ($expiryOptions as $value => $label): ?>
                                                <option value="<?= F::e($value) ?>">
                                                    <?= F::e($label) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            Generate
                                        </button>
                                    </form>
                                <?php elseif (!empty($request['token_is_active'])): ?>
                                    <span class="text-muted small">
                                        Token masih aktif. Revoke dulu jika ingin membuat ulang.
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Tidak tersedia</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <strong>Riwayat Token</strong>
    </div>

    <div class="card-body">
        <?php if (empty($tokens)): ?>
            <div class="alert alert-info mb-0">
                Belum ada token dibuat.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th>Pemilih</th>
                        <th>Kode Verifikasi</th>
                        <th>Status</th>
                        <th>Expired</th>
                        <th>Used At</th>
                        <th>Revoked At</th>
                        <th>Dibuat Oleh</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($tokens as $token): ?>
                        <tr>
                            <td>
                                <strong><?= F::dash($token['voter_name'] ?? null) ?></strong>

                                <div class="small text-muted">
                                    <?= F::dash($token['voter_code'] ?? null) ?>
                                </div>
                            </td>

                            <td>
                                <?= F::dash($token['verification_code'] ?? null) ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e($token['status_badge'] ?? 'secondary') ?>">
                                    <?= F::dash($token['status_text'] ?? null) ?>
                                </span>
                            </td>

                            <td>
                                <?= F::dateTime($token['expires_at'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::dateTime($token['used_at'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::dateTime($token['revoked_at'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::dash($token['created_by_name'] ?? null) ?>
                            </td>

                            <td>
                                <?php if (!empty($token['can_revoke'])): ?>
                                    <form
                                        method="post"
                                        action="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/remote-tokens/<?= F::e($token['id']) ?>/revoke"
                                        onsubmit="return confirm('Yakin revoke token ini?');"
                                    >
                                        <?= Csrf::field() ?>

                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Revoke
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>

                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function copyGeneratedLink() {
    const input = document.getElementById('generated-token-link');

    if (!input) {
        return;
    }

    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand('copy');

    alert('Link token berhasil dicopy.');
}
</script>