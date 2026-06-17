<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function tokenStatus(array $row): array
{
    if (empty($row['token_id'])) {
        return ['Belum Ada Token', 'secondary'];
    }

    if (!empty($row['token_used_at'])) {
        return ['Sudah Dipakai', 'success'];
    }

    if (!empty($row['token_revoked_at'])) {
        return ['Revoked', 'danger'];
    }

    if (!empty($row['token_expires_at']) && strtotime($row['token_expires_at']) <= time()) {
        return ['Expired', 'warning'];
    }

    return ['Aktif', 'primary'];
}

function tokenRowStatus(array $token): array
{
    if (!empty($token['used_at'])) {
        return ['Sudah Dipakai', 'success'];
    }

    if (!empty($token['revoked_at'])) {
        return ['Revoked', 'danger'];
    }

    if (!empty($token['expires_at']) && strtotime($token['expires_at']) <= time()) {
        return ['Expired', 'warning'];
    }

    return ['Aktif', 'primary'];
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Token Voting Remote</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/remote-tokens" class="btn btn-secondary">
        Kembali
    </a>
</div>

<?php if ($election['status'] !== 'open'): ?>
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
                value="<?= htmlspecialchars($generatedLink) ?>"
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
                        <?php [$statusText, $statusBadge] = tokenStatus($request); ?>

                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($request['verification_code']) ?></strong>
                            </td>

                            <td>
                                <strong><?= htmlspecialchars($request['voter_name']) ?></strong>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($request['voter_code']) ?>
                                </div>
                                <div class="small text-muted">
                                    HP: <?= htmlspecialchars($request['phone'] ?? '-') ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars($request['allowed_channel']) ?>
                            </td>

                            <td>
                                <?php if ((int) $request['has_voted'] === 1): ?>
                                    <span class="badge bg-success">Sudah Coblos</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= $statusBadge ?>">
                                    <?= htmlspecialchars($statusText) ?>
                                </span>
                            </td>

                            <td>
                                <?= !empty($request['token_expires_at']) ? date('d/m/Y H:i', strtotime($request['token_expires_at'])) : '-' ?>
                            </td>

                            <td>
                                <?php if ($election['status'] === 'open' && (int) $request['has_voted'] !== 1): ?>
                                    <?php if (empty($request['token_id']) || !empty($request['token_used_at']) || !empty($request['token_revoked_at']) || (!empty($request['token_expires_at']) && strtotime($request['token_expires_at']) <= time())): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-tokens/<?= $request['id'] ?>/generate"
                                            class="d-flex gap-1"
                                            onsubmit="return confirm('Generate token remote untuk pemilih ini?');"
                                        >
                                            <?= Csrf::field() ?>

                                            <select name="expires_minutes" class="form-select form-select-sm">
                                                <option value="5">5 menit</option>
                                                <option value="15">15 menit</option>
                                                <option value="30">30 menit</option>
                                                <option value="60">60 menit</option>
                                            </select>

                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Generate
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted small">
                                            Token masih aktif. Revoke dulu jika ingin membuat ulang.
                                        </span>
                                    <?php endif; ?>
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
                        <?php [$statusText, $statusBadge] = tokenRowStatus($token); ?>

                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($token['voter_name']) ?></strong>
                                <div class="small text-muted">
                                    <?= htmlspecialchars($token['voter_code']) ?>
                                </div>
                            </td>

                            <td>
                                <?= htmlspecialchars($token['verification_code'] ?? '-') ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= $statusBadge ?>">
                                    <?= htmlspecialchars($statusText) ?>
                                </span>
                            </td>

                            <td>
                                <?= $token['expires_at'] ? date('d/m/Y H:i', strtotime($token['expires_at'])) : '-' ?>
                            </td>

                            <td>
                                <?= $token['used_at'] ? date('d/m/Y H:i', strtotime($token['used_at'])) : '-' ?>
                            </td>

                            <td>
                                <?= $token['revoked_at'] ? date('d/m/Y H:i', strtotime($token['revoked_at'])) : '-' ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($token['created_by_name'] ?? '-') ?>
                            </td>

                            <td>
                                <?php if (empty($token['used_at']) && empty($token['revoked_at']) && strtotime($token['expires_at']) > time()): ?>
                                    <form 
                                        method="post" 
                                        action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-tokens/<?= $token['id'] ?>/revoke"
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