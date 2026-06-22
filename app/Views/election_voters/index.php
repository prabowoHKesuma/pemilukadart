<?php

use App\Core\Csrf;
use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Daftar Pemilih Pemilihan</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= F::dash($election['title'] ?? null) ?></strong>
        </div>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= F::e($appUrl) ?>/elections" class="btn btn-secondary">
            Kembali
        </a>

        <?php if (!empty($canModifyVoters)): ?>
            <a href="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/voters/create" class="btn btn-primary">
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
                    <?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Pemilih</div>
                <div class="h5 mb-0">
                    <?= F::e($totalVoters ?? 0) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sudah Mencoblos</div>
                <div class="h5 mb-0">
                    <?= F::e($totalVoted ?? 0) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Belum Mencoblos</div>
                <div class="h5 mb-0">
                    <?= F::e($totalNotVoted ?? 0) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($isElectionLocked)): ?>
    <div class="alert alert-warning">
        Pemilihan sudah berstatus <strong><?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?></strong>.
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
                                <strong><?= F::dash($item['voter_code'] ?? null) ?></strong>
                            </td>

                            <td>
                                <?= F::dash($item['name'] ?? null) ?>

                                <?php if (!empty($item['is_master_inactive'])): ?>
                                    <div class="small text-danger">
                                        Pemilih nonaktif di master data
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?= F::nl2brSafe($item['address'] ?? null) ?>
                            </td>

                            <td>
                                <?= F::e(($item['rt'] ?: '-') . ' / ' . ($item['rw'] ?: '-')) ?>
                            </td>

                            <td>
                                <?= F::dash($item['phone'] ?? null) ?>
                            </td>

                            <td>
                                <span class="badge bg-<?= F::e(F::channelBadgeClass($item['allowed_channel'] ?? null)) ?>">
                                    <?= F::e(F::channelLabel($item['allowed_channel'] ?? null)) ?>
                                </span>
                            </td>

                            <td>
                                <?php if ((int) ($item['has_voted'] ?? 0) === 1): ?>
                                    <span class="badge bg-success">Sudah</span>

                                    <?php if (!empty($item['voted_at'])): ?>
                                        <div class="small text-muted">
                                            <?= F::dateTime($item['voted_at']) ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Belum</span>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php if (!empty($item['can_manage_row'])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <form
                                            method="post"
                                            action="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/voters/<?= F::e($item['id']) ?>/channel"
                                            class="d-inline"
                                        >
                                            <?= Csrf::field() ?>

                                            <select
                                                name="allowed_channel"
                                                class="form-select form-select-sm"
                                                onchange="this.form.submit()"
                                            >
                                                <?php foreach ($channelOptions as $value => $label): ?>
                                                    <option
                                                        value="<?= F::e($value) ?>"
                                                        <?= ($item['allowed_channel'] ?? '') === $value ? 'selected' : '' ?>
                                                    >
                                                        <?= F::e($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </form>

                                        <form
                                            method="post"
                                            action="<?= F::e($appUrl) ?>/elections/<?= F::e($election['id']) ?>/voters/<?= F::e($item['id']) ?>/delete"
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