<?php

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function channelLabel(string $channel): string
{
    return match ($channel) {
        'tps' => 'TPS',
        'remote' => 'Remote',
        'both' => 'TPS / Remote',
        default => $channel,
    };
}

function channelBadge(string $channel): string
{
    return match ($channel) {
        'tps' => 'primary',
        'remote' => 'warning',
        'both' => 'info',
        default => 'secondary',
    };
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Daftar Pemilih Pemilihan</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars($appUrl) ?>/elections" class="btn btn-secondary">
            Kembali
        </a>

        <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true) && $election['status'] === 'draft'): ?>
            <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/voters/create" class="btn btn-primary">
                + Tambah Pemilih
            </a>
        <?php endif; ?>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status Pemilihan</div>
                <div class="h5 mb-0">
                    <?= htmlspecialchars(strtoupper($election['status'])) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Pemilih</div>
                <div class="h5 mb-0">
                    <?= htmlspecialchars((string) $totalVoters) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sudah Mencoblos</div>
                <div class="h5 mb-0">
                    <?= htmlspecialchars((string) $totalVoted) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Belum Mencoblos</div>
                <div class="h5 mb-0">
                    <?= htmlspecialchars((string) ($totalVoters - $totalVoted)) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($election['status'] !== 'draft'): ?>
    <div class="alert alert-warning">
        Pemilihan sudah berstatus <strong><?= htmlspecialchars(strtoupper($election['status'])) ?></strong>.
        Daftar pemilih dikunci. Perubahan hanya boleh dilakukan saat status masih draft.
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <?php if (empty($electionVoters)): ?>
            <div class="alert alert-info mb-0">
                Belum ada pemilih yang dimasukkan ke pemilihan ini.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode</th>
                        <th>Nama Pemilih</th>
                        <th>Alamat</th>
                        <th>RT/RW</th>
                        <th>No HP</th>
                        <th>Channel</th>
                        <th>Status Coblos</th>
                        <th style="width: 230px;">Aksi</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($electionVoters as $index => $item): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>

                            <td>
                                <strong><?= htmlspecialchars($item['voter_code']) ?></strong>
                            </td>

                            <td>
                                <?= htmlspecialchars($item['name']) ?>

                                <?php if ((int) $item['is_active'] !== 1): ?>
                                    <div class="small text-danger">
                                        Pemilih nonaktif di master data
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= !empty($item['address']) ? nl2br(htmlspecialchars($item['address'])) : '-' ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(($item['rt'] ?: '-') . ' / ' . ($item['rw'] ?: '-')) ?>
                            </td>

                            <td>
                                <?= !empty($item['phone']) ? htmlspecialchars($item['phone']) : '-' ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= channelBadge($item['allowed_channel']) ?>">
                                    <?= htmlspecialchars(channelLabel($item['allowed_channel'])) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ((int) $item['has_voted'] === 1): ?>
                                    <span class="badge bg-success">Sudah</span>
                                    <div class="small text-muted">
                                        <?= $item['voted_at'] ? date('d/m/Y H:i', strtotime($item['voted_at'])) : '' ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (in_array(Auth::role(), ['superadmin', 'panitia'], true) && $election['status'] === 'draft' && (int) $item['has_voted'] !== 1): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/voters/<?= $item['id'] ?>/channel"
                                            class="d-inline"
                                        >
                                            <?= Csrf::field() ?>

                                            <select 
                                                name="allowed_channel" 
                                                class="form-select form-select-sm"
                                                onchange="this.form.submit()"
                                            >
                                                <option value="tps" <?= $item['allowed_channel'] === 'tps' ? 'selected' : '' ?>>TPS</option>
                                                <option value="remote" <?= $item['allowed_channel'] === 'remote' ? 'selected' : '' ?>>Remote</option>
                                                <option value="both" <?= $item['allowed_channel'] === 'both' ? 'selected' : '' ?>>TPS / Remote</option>
                                            </select>
                                        </form>

                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/voters/<?= $item['id'] ?>/delete"
                                            onsubmit="return confirm('Yakin hapus pemilih ini dari daftar pemilihan?');"
                                        >
                                            <?= Csrf::field() ?>

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small">Terkunci</span>
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