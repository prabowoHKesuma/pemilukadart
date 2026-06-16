<?php

use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

function channelText(string $channel): string
{
    return match ($channel) {
        'tps' => 'TPS',
        'remote' => 'Remote',
        'both' => 'TPS / Remote',
        default => $channel,
    };
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Cari Pemilih TPS</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/tps-voting" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="get" action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/tps-voting">
            <label class="form-label">Cari berdasarkan kode pemilih, nama, atau nomor HP</label>

            <div class="input-group">
                <input 
                    type="text" 
                    name="q" 
                    class="form-control form-control-lg"
                    value="<?= htmlspecialchars($keyword) ?>"
                    placeholder="Contoh: PM-260616, Ahmad, 08xxxx"
                    autofocus
                >

                <button type="submit" class="btn btn-primary">
                    Cari
                </button>
            </div>
        </form>
    </div>
</div>

<?php if ($keyword === ''): ?>
    <div class="alert alert-info">
        Masukkan kode pemilih, nama, atau nomor HP untuk mulai mencari.
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body">
            <?php if (empty($results)): ?>
                <div class="alert alert-warning mb-0">
                    Tidak ada pemilih ditemukan untuk kata kunci:
                    <strong><?= htmlspecialchars($keyword) ?></strong>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama Pemilih</th>
                            <th>Alamat</th>
                            <th>RT/RW</th>
                            <th>No HP</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th style="width: 180px;">Aksi</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($results as $item): ?>
                            <?php
                                $canVote = true;
                                $reason = '';

                                if ((int) $item['is_active'] !== 1) {
                                    $canVote = false;
                                    $reason = 'Pemilih nonaktif';
                                } elseif ((int) $item['has_voted'] === 1) {
                                    $canVote = false;
                                    $reason = 'Sudah mencoblos';
                                } elseif (!in_array($item['allowed_channel'], ['tps', 'both'], true)) {
                                    $canVote = false;
                                    $reason = 'Bukan channel TPS';
                                }
                            ?>

                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($item['voter_code']) ?></strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars($item['name']) ?>
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
                                    <?= htmlspecialchars(channelText($item['allowed_channel'])) ?>
                                </td>

                                <td>
                                    <?php if ($canVote): ?>
                                        <span class="badge bg-success">Bisa Coblos</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($reason) ?></span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($canVote): ?>
                                        <a 
                                            href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/tps-voting/<?= $item['id'] ?>/ballot" 
                                            class="btn btn-sm btn-primary"
                                        >
                                            Buka Halaman Coblos
                                        </a>
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
<?php endif; ?>