<?php

use App\Core\Csrf;
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
        <h1 class="h3 mb-0">Validasi Pemilih TPS</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= htmlspecialchars($appUrl) ?>/tps-booth" target="_blank" class="btn btn-success">
            Buka Mode Bilik
        </a>

        <a href="<?= htmlspecialchars($appUrl) ?>/tps-voting" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

<?php if (!empty($generatedBoothCode)): ?>
    <div class="alert alert-success">
        <div class="mb-1">
            <strong>Kode Bilik Berhasil Dibuat</strong>
        </div>

        <div class="row align-items-center">
            <div class="col-md-4">
                <div class="text-muted small">Pemilih</div>
                <div><strong><?= htmlspecialchars($generatedBoothVoter ?? '-') ?></strong></div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Kode Bilik</div>
                <div class="display-5 fw-bold">
                    <?= htmlspecialchars($generatedBoothCode) ?>
                </div>
            </div>

            <div class="col-md-4">
                <div class="text-muted small">Expired</div>
                <div>
                    <strong>
                        <?= !empty($generatedBoothExpiresAt) ? date('d/m/Y H:i:s', strtotime($generatedBoothExpiresAt)) : '-' ?>
                    </strong>
                </div>
            </div>
        </div>

        <hr>

        <div class="mb-0">
            Berikan kode ini kepada pemilih. Pemilih memasukkan kode ini di komputer/tablet bilik.
        </div>
    </div>
<?php endif; ?>

<div class="alert alert-info">
    <strong>Alur TPS:</strong>
    validasi data pemilih di meja admin, lalu klik <strong>Buat Kode Bilik</strong>.
    Pemilih menggunakan kode tersebut di halaman <strong>Mode Bilik</strong>.
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
                            <th style="width: 280px;">Aksi</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($results as $item): ?>
                            <?php
                                $canGenerate = true;
                                $reason = '';

                                if ((int) $item['is_active'] !== 1) {
                                    $canGenerate = false;
                                    $reason = 'Pemilih nonaktif';
                                } elseif ((int) $item['has_voted'] === 1) {
                                    $canGenerate = false;
                                    $reason = 'Sudah mencoblos';
                                } elseif (!in_array($item['allowed_channel'], ['tps', 'both'], true)) {
                                    $canGenerate = false;
                                    $reason = 'Bukan channel TPS';
                                } elseif (!empty($item['active_booth_token_id'])) {
                                    $canGenerate = false;
                                    $reason = 'Kode bilik aktif';
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
                                    <?php if ($canGenerate): ?>
                                        <span class="badge bg-success">Siap Validasi</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($reason) ?></span>

                                        <?php if (!empty($item['active_booth_expires_at'])): ?>
                                            <div class="small text-muted">
                                                Expired:
                                                <?= date('d/m/Y H:i:s', strtotime($item['active_booth_expires_at'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if ($canGenerate): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/tps-voting/<?= $item['id'] ?>/generate-code"
                                            class="d-flex gap-1"
                                            onsubmit="return confirm('Data pemilih sudah divalidasi secara fisik? Buat kode bilik sekarang?');"
                                        >
                                            <?= Csrf::field() ?>

                                            <select name="expires_minutes" class="form-select form-select-sm">
                                                <option value="5">5 menit</option>
                                                <option value="10" selected>10 menit</option>
                                                <option value="15">15 menit</option>
                                                <option value="30">30 menit</option>
                                            </select>

                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Buat Kode Bilik
                                            </button>
                                        </form>
                                    <?php elseif (!empty($item['active_booth_token_id'])): ?>
                                        <form 
                                            method="post" 
                                            action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/tps-voting/booth-token/<?= $item['active_booth_token_id'] ?>/revoke"
                                            onsubmit="return confirm('Yakin revoke kode bilik aktif ini?');"
                                        >
                                            <?= Csrf::field() ?>

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Revoke Kode
                                            </button>
                                        </form>
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