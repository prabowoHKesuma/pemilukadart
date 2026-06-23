<?php

use App\Core\ViewFormatter as F;

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Detail Hasil Pemilihan</h1>
        <div class="text-muted small">
            <?= F::dash($election['title'] ?? null) ?>
        </div>
    </div>

    <div class="d-flex gap-2">
        <?php if (!empty($canPrint)): ?>
            <a
                href="<?= F::e($appUrl . $print_url) ?>"
                class="btn btn-dark"
                target="_blank"
            >
                Cetak Berita Acara
            </a>

            <a
                href="<?= F::e($appUrl . $export_url) ?>"
                class="btn btn-success"
            >
                Export CSV
            </a>
        <?php endif; ?>

        <a href="<?= F::e($appUrl . $back_url) ?>" class="btn btn-secondary">
            Kembali
        </a>
    </div>
</div>

<?php if (!empty($isElectionOpen)): ?>
    <div class="alert alert-warning">
        Pemilihan masih berstatus <strong>OPEN</strong>. Data di halaman ini adalah hasil sementara.
    </div>
<?php endif; ?>

<?php if (!empty($summary['ballot_mismatch'])): ?>
    <div class="alert alert-danger">
        <strong>Peringatan integritas:</strong>
        jumlah pemilih yang tercatat sudah mencoblos adalah
        <strong><?= F::e($summary['total_voted']) ?></strong>,
        sedangkan jumlah suara masuk di tabel ballots adalah
        <strong><?= F::e($summary['total_ballots']) ?></strong>.
        Seharusnya angka ini sama.
    </div>
<?php endif; ?>

<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status</div>
                <div class="h5 mb-0">
                    <span class="badge bg-<?= F::e(F::electionStatusBadgeClass($election['status'] ?? null)) ?>">
                        <?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Kandidat</div>
                <div class="h4 mb-0"><?= F::e($summary['total_candidates']) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Pemilih</div>
                <div class="h4 mb-0"><?= F::e($summary['total_voters']) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Suara Masuk</div>
                <div class="h4 mb-0"><?= F::e($summary['total_ballots']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sudah Mencoblos</div>
                <div class="h4 mb-0 text-success"><?= F::e($summary['total_voted']) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Belum Mencoblos</div>
                <div class="h4 mb-0 text-secondary"><?= F::e($summary['total_not_voted']) ?></div>
            </div>
        </div>
    </div>

    <div class="col-md-4 mb-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Partisipasi</div>
                <div class="h4 mb-2"><?= F::e($summary['turnout_percent']) ?>%</div>

                <div class="progress" style="height: 10px;">
                    <div
                        class="progress-bar"
                        role="progressbar"
                        style="width: <?= F::e($summary['turnout_percent']) ?>%;"
                        aria-valuenow="<?= F::e($summary['turnout_percent']) ?>"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header">
        <strong>Perolehan Suara Kandidat</strong>
    </div>

    <div class="card-body">
        <?php if (empty($candidateResults)): ?>
            <div class="alert alert-info mb-0">
                Belum ada kandidat.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($candidateResults as $candidate): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <div class="card h-100 shadow-sm">
                            <div class="card-body text-center">
                                <div class="mb-2">
                                    <span class="badge bg-dark fs-6">
                                        No. <?= F::e($candidate['number_order'] ?? '-') ?>
                                    </span>
                                </div>

                                <?php if (!empty($candidate['photo_url'])): ?>
                                    <img
                                        src="<?= F::e($appUrl . $candidate['photo_url']) ?>"
                                        alt="Foto kandidat"
                                        class="rounded border mb-3"
                                        style="width: 130px; height: 130px; object-fit: cover;"
                                    >
                                <?php else: ?>
                                    <div
                                        class="bg-light border rounded d-flex align-items-center justify-content-center text-muted mx-auto mb-3"
                                        style="width: 130px; height: 130px;"
                                    >
                                        No Foto
                                    </div>
                                <?php endif; ?>

                                <h4 class="mb-1"><?= F::dash($candidate['name'] ?? null) ?></h4>

                                <?php if (($candidate['active_label'] ?? '') === 'Nonaktif'): ?>
                                    <div class="mb-2">
                                        <span class="badge bg-secondary">Nonaktif</span>
                                    </div>
                                <?php endif; ?>

                                <div class="display-6 fw-bold mb-1">
                                    <?= F::e($candidate['total_votes']) ?>
                                </div>

                                <div class="text-muted mb-2">
                                    suara / <?= F::e($candidate['vote_percent']) ?>%
                                </div>

                                <div class="progress" style="height: 10px;">
                                    <div
                                        class="progress-bar"
                                        role="progressbar"
                                        style="width: <?= F::e($candidate['vote_percent']) ?>%;"
                                        aria-valuenow="<?= F::e($candidate['vote_percent']) ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered table-striped align-middle">
                    <thead>
                    <tr>
                        <th style="width: 80px;">No Urut</th>
                        <th>Nama Kandidat</th>
                        <th style="width: 150px;">Suara</th>
                        <th style="width: 150px;">Persentase</th>
                    </tr>
                    </thead>

                    <tbody>
                    <?php foreach ($candidateResults as $candidate): ?>
                        <tr>
                            <td><?= F::e($candidate['number_order'] ?? '-') ?></td>
                            <td><?= F::dash($candidate['name'] ?? null) ?></td>
                            <td><strong><?= F::e($candidate['total_votes']) ?></strong></td>
                            <td><?= F::e($candidate['vote_percent']) ?>%</td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <strong>Suara Masuk Berdasarkan Channel</strong>
            </div>

            <div class="card-body">
                <?php if (empty($channelResults)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada suara masuk.
                    </div>
                <?php else: ?>
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Channel Suara</th>
                            <th>Total Suara</th>
                            <th>Persentase</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($channelResults as $row): ?>
                            <tr>
                                <td><?= F::dash($row['channel_label'] ?? null) ?></td>
                                <td><?= F::e($row['total_votes'] ?? 0) ?></td>
                                <td><?= F::e($row['vote_percent'] ?? 0) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-md-6 mb-3">
        <div class="card h-100">
            <div class="card-header">
                <strong>Partisipasi Berdasarkan Hak Channel</strong>
            </div>

            <div class="card-body">
                <?php if (empty($turnoutByChannel)): ?>
                    <div class="alert alert-info mb-0">
                        Belum ada daftar pemilih.
                    </div>
                <?php else: ?>
                    <table class="table table-bordered align-middle mb-0">
                        <thead>
                        <tr>
                            <th>Hak Channel</th>
                            <th>Total Pemilih</th>
                            <th>Sudah Coblos</th>
                            <th>Partisipasi</th>
                        </tr>
                        </thead>

                        <tbody>
                        <?php foreach ($turnoutByChannel as $row): ?>
                            <tr>
                                <td><?= F::dash($row['channel_label'] ?? null) ?></td>
                                <td><?= F::e($row['total_voters'] ?? 0) ?></td>
                                <td><?= F::e($row['total_voted'] ?? 0) ?></td>
                                <td><?= F::e($row['turnout_percent'] ?? 0) ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>