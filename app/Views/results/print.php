<?php

function printPercentValue(int $value, int $total): float
{
    if ($total <= 0) {
        return 0;
    }

    return round(($value / $total) * 100, 2);
}

function printChannelLabel(string $channel): string
{
    return match ($channel) {
        'tps' => 'TPS',
        'remote' => 'Remote',
        'both' => 'TPS / Remote',
        default => $channel,
    };
}

$totalVoters = (int) ($summary['total_voters'] ?? 0);
$totalVoted = (int) ($summary['total_voted'] ?? 0);
$totalBallots = (int) ($summary['total_ballots'] ?? 0);
$totalCandidates = (int) ($summary['total_candidates'] ?? 0);
$totalNotVoted = max($totalVoters - $totalVoted, 0);
$turnoutPercent = printPercentValue($totalVoted, $totalVoters);
$ballotMismatch = $totalVoted !== $totalBallots;

?>

<div class="text-center mb-3">
    <h2>BERITA ACARA REKAPITULASI HASIL PEMILIHAN</h2>
    <h3><?= htmlspecialchars($election['title']) ?></h3>
    <div class="text-muted">
        Dicetak pada: <?= date('d/m/Y H:i:s') ?>
    </div>
</div>

<?php if ($election['status'] === 'open'): ?>
    <div class="warning">
        <strong>PERHATIAN:</strong>
        Pemilihan masih berstatus OPEN. Dokumen ini adalah hasil sementara.
    </div>
<?php endif; ?>

<?php if ($ballotMismatch): ?>
    <div class="warning">
        <strong>PERINGATAN INTEGRITAS:</strong>
        Jumlah pemilih yang tercatat sudah mencoblos adalah
        <?= htmlspecialchars((string) $totalVoted) ?>,
        sedangkan jumlah suara masuk adalah
        <?= htmlspecialchars((string) $totalBallots) ?>.
        Seharusnya angka ini sama.
    </div>
<?php endif; ?>

<div class="border-box mb-3">
    <table class="info-table">
        <tr>
            <td style="width: 180px;">Nama Pemilihan</td>
            <td style="width: 10px;">:</td>
            <td><strong><?= htmlspecialchars($election['title']) ?></strong></td>
        </tr>

        <tr>
            <td>Deskripsi</td>
            <td>:</td>
            <td><?= nl2br(htmlspecialchars($election['description'] ?? '-')) ?></td>
        </tr>

        <tr>
            <td>Status</td>
            <td>:</td>
            <td><strong><?= htmlspecialchars(strtoupper($election['status'])) ?></strong></td>
        </tr>

        <tr>
            <td>Waktu Mulai</td>
            <td>:</td>
            <td><?= $election['start_at'] ? date('d/m/Y H:i', strtotime($election['start_at'])) : '-' ?></td>
        </tr>

        <tr>
            <td>Waktu Selesai</td>
            <td>:</td>
            <td><?= $election['end_at'] ? date('d/m/Y H:i', strtotime($election['end_at'])) : '-' ?></td>
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
        <td><?= htmlspecialchars((string) $totalCandidates) ?></td>
    </tr>

    <tr>
        <td>Total Pemilih Terdaftar</td>
        <td><?= htmlspecialchars((string) $totalVoters) ?></td>
    </tr>

    <tr>
        <td>Sudah Mencoblos</td>
        <td><?= htmlspecialchars((string) $totalVoted) ?></td>
    </tr>

    <tr>
        <td>Belum Mencoblos</td>
        <td><?= htmlspecialchars((string) $totalNotVoted) ?></td>
    </tr>

    <tr>
        <td>Total Suara Masuk</td>
        <td><?= htmlspecialchars((string) $totalBallots) ?></td>
    </tr>

    <tr>
        <td>Persentase Partisipasi</td>
        <td><?= htmlspecialchars((string) $turnoutPercent) ?>%</td>
    </tr>

    <tr>
        <td>Status Integritas Data</td>
        <td>
            <strong><?= $ballotMismatch ? 'TIDAK SAMA' : 'OK' ?></strong>
        </td>
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
        <?php
            $votes = (int) $candidate['total_votes'];
            $percent = printPercentValue($votes, $totalBallots);
        ?>

        <tr>
            <td><?= htmlspecialchars((string) $candidate['number_order']) ?></td>
            <td><strong><?= htmlspecialchars($candidate['name']) ?></strong></td>
            <td><?= (int) $candidate['is_active'] === 1 ? 'Aktif' : 'Nonaktif' ?></td>
            <td><?= htmlspecialchars((string) $votes) ?></td>
            <td><?= htmlspecialchars((string) $percent) ?>%</td>
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
            <?php
                $votes = (int) $row['total_votes'];
                $percent = printPercentValue($votes, $totalBallots);
            ?>

            <tr>
                <td><?= htmlspecialchars(printChannelLabel($row['vote_channel'])) ?></td>
                <td><?= htmlspecialchars((string) $votes) ?></td>
                <td><?= htmlspecialchars((string) $percent) ?>%</td>
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
            <?php
                $channelVoters = (int) $row['total_voters'];
                $channelVoted = (int) $row['total_voted'];
                $percent = printPercentValue($channelVoted, $channelVoters);
            ?>

            <tr>
                <td><?= htmlspecialchars(printChannelLabel($row['allowed_channel'])) ?></td>
                <td><?= htmlspecialchars((string) $channelVoters) ?></td>
                <td><?= htmlspecialchars((string) $channelVoted) ?></td>
                <td><?= htmlspecialchars((string) $percent) ?>%</td>
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