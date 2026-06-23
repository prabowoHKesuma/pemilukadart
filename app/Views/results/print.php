<?php

use App\Core\ViewFormatter as F;

?>

<div class="text-center mb-3">
    <h2>BERITA ACARA REKAPITULASI HASIL PEMILIHAN</h2>
    <h3><?= F::dash($election['title'] ?? null) ?></h3>
    <div class="text-muted">
        Dicetak pada: <?= F::dash($printedAt ?? null) ?>
    </div>
</div>

<?php if (!empty($isElectionOpen)): ?>
    <div class="warning">
        <strong>PERHATIAN:</strong>
        Pemilihan masih berstatus OPEN. Dokumen ini adalah hasil sementara.
    </div>
<?php endif; ?>

<?php if (!empty($summary['ballot_mismatch'])): ?>
    <div class="warning">
        <strong>PERINGATAN INTEGRITAS:</strong>
        Jumlah pemilih yang tercatat sudah mencoblos adalah
        <?= F::e($summary['total_voted']) ?>,
        sedangkan jumlah suara masuk adalah
        <?= F::e($summary['total_ballots']) ?>.
        Seharusnya angka ini sama.
    </div>
<?php endif; ?>

<div class="border-box mb-3">
    <table class="info-table">
        <tr>
            <td style="width: 180px;">Nama Pemilihan</td>
            <td style="width: 10px;">:</td>
            <td><strong><?= F::dash($election['title'] ?? null) ?></strong></td>
        </tr>

        <tr>
            <td>Deskripsi</td>
            <td>:</td>
            <td><?= F::nl2brSafe($election['description'] ?? null) ?></td>
        </tr>

        <tr>
            <td>Status</td>
            <td>:</td>
            <td><strong><?= F::e(strtoupper((string) ($election['status'] ?? '-'))) ?></strong></td>
        </tr>

        <tr>
            <td>Waktu Mulai</td>
            <td>:</td>
            <td><?= F::dateTime($election['start_at'] ?? null) ?></td>
        </tr>

        <tr>
            <td>Waktu Selesai</td>
            <td>:</td>
            <td><?= F::dateTime($election['end_at'] ?? null) ?></td>
        </tr>
    </table>
</div>

<h3 class="mb-2">A. Ringkasan Pemilihan</h3>

<table class="data-table mb-3">
    <thead>
    <tr>
        <th>Uraian</th>
        <th style="width: 160px;">Jumlah</th>
    </tr>
    </thead>

    <tbody>
    <tr>
        <td>Total Kandidat</td>
        <td><?= F::e($summary['total_candidates']) ?></td>
    </tr>

    <tr>
        <td>Total Pemilih Terdaftar</td>
        <td><?= F::e($summary['total_voters']) ?></td>
    </tr>

    <tr>
        <td>Sudah Mencoblos</td>
        <td><?= F::e($summary['total_voted']) ?></td>
    </tr>

    <tr>
        <td>Belum Mencoblos</td>
        <td><?= F::e($summary['total_not_voted']) ?></td>
    </tr>

    <tr>
        <td>Total Suara Masuk</td>
        <td><?= F::e($summary['total_ballots']) ?></td>
    </tr>

    <tr>
        <td>Persentase Partisipasi</td>
        <td><?= F::e($summary['turnout_percent']) ?>%</td>
    </tr>

    <tr>
        <td>Status Integritas Data</td>
        <td><strong><?= F::e($summary['integrity_label']) ?></strong></td>
    </tr>
    </tbody>
</table>

<h3 class="mb-2">B. Perolehan Suara Kandidat</h3>

<table class="data-table mb-3">
    <thead>
    <tr>
        <th style="width: 80px;">No Urut</th>
        <th>Nama Kandidat</th>
        <th style="width: 120px;">Status</th>
        <th style="width: 120px;">Suara</th>
        <th style="width: 120px;">Persentase</th>
    </tr>
    </thead>

    <tbody>
    <?php foreach ($candidateResults as $candidate): ?>
        <tr>
            <td><?= F::e($candidate['number_order'] ?? '-') ?></td>
            <td><strong><?= F::dash($candidate['name'] ?? null) ?></strong></td>
            <td><?= F::e($candidate['active_label'] ?? '-') ?></td>
            <td><?= F::e($candidate['total_votes'] ?? 0) ?></td>
            <td><?= F::e($candidate['vote_percent'] ?? 0) ?>%</td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<h3 class="mb-2">C. Suara Masuk Berdasarkan Channel</h3>

<table class="data-table mb-3">
    <thead>
    <tr>
        <th>Channel Suara</th>
        <th style="width: 140px;">Total Suara</th>
        <th style="width: 140px;">Persentase</th>
    </tr>
    </thead>

    <tbody>
    <?php if (empty($channelResults)): ?>
        <tr>
            <td colspan="3">Belum ada suara masuk.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($channelResults as $row): ?>
            <tr>
                <td><?= F::dash($row['channel_label'] ?? null) ?></td>
                <td><?= F::e($row['total_votes'] ?? 0) ?></td>
                <td><?= F::e($row['vote_percent'] ?? 0) ?>%</td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<h3 class="mb-2">D. Partisipasi Berdasarkan Hak Channel</h3>

<table class="data-table mb-3">
    <thead>
    <tr>
        <th>Hak Channel</th>
        <th style="width: 140px;">Total Pemilih</th>
        <th style="width: 140px;">Sudah Coblos</th>
        <th style="width: 140px;">Partisipasi</th>
    </tr>
    </thead>

    <tbody>
    <?php if (empty($turnoutByChannel)): ?>
        <tr>
            <td colspan="4">Belum ada daftar pemilih.</td>
        </tr>
    <?php else: ?>
        <?php foreach ($turnoutByChannel as $row): ?>
            <tr>
                <td><?= F::dash($row['channel_label'] ?? null) ?></td>
                <td><?= F::e($row['total_voters'] ?? 0) ?></td>
                <td><?= F::e($row['total_voted'] ?? 0) ?></td>
                <td><?= F::e($row['turnout_percent'] ?? 0) ?>%</td>
            </tr>
        <?php endforeach; ?>
    <?php endif; ?>
    </tbody>
</table>

<h3 class="mb-2">E. Pernyataan</h3>

<p>
    Dengan ini panitia menyatakan bahwa rekapitulasi hasil pemilihan di atas
    dicetak berdasarkan data yang tercatat pada sistem pemilihan elektronik.
</p>

<p>
    Berita acara ini dibuat untuk digunakan sebagaimana mestinya sebagai arsip
    dan bahan pengesahan hasil pemilihan.
</p>

<table class="signature-table">
    <tr>
        <td>
            Ketua Panitia
            <div class="signature-space"></div>
            ( __________________ )
        </td>

        <td>
            Saksi 1
            <div class="signature-space"></div>
            ( __________________ )
        </td>

        <td>
            Saksi 2
            <div class="signature-space"></div>
            ( __________________ )
        </td>

        <td>
            Perwakilan Warga
            <div class="signature-space"></div>
            ( __________________ )
        </td>
    </tr>
</table>