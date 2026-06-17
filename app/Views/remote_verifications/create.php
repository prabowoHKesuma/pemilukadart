<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Buat Request Verifikasi Remote</h1>
        <div class="text-muted small">
            Pemilihan: <strong><?= htmlspecialchars($election['title']) ?></strong>
        </div>
    </div>

    <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="alert alert-info">
    Setelah request dibuat, sistem akan membuat kode verifikasi.
    Minta pemilih selfie dengan memegang kertas berisi kode tersebut.
</div>

<div class="card">
    <div class="card-body">
        <?php if (empty($availableVoters)): ?>
            <div class="alert alert-warning mb-0">
                Tidak ada pemilih remote tersedia.
                Pastikan pemilih sudah di-assign ke pemilihan ini dengan channel <strong>Remote</strong> atau <strong>TPS / Remote</strong>.
            </div>
        <?php else: ?>
            <form method="post" action="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications/store">
                <?= Csrf::field() ?>

                <div class="mb-3">
                    <label class="form-label">Pilih Pemilih Remote</label>

                    <select name="election_voter_id" class="form-select" required>
                        <option value="">-- Pilih Pemilih --</option>

                        <?php foreach ($availableVoters as $voter): ?>
                            <option value="<?= htmlspecialchars((string) $voter['election_voter_id']) ?>">
                                <?= htmlspecialchars($voter['voter_code']) ?>
                                -
                                <?= htmlspecialchars($voter['name']) ?>
                                -
                                Channel: <?= htmlspecialchars($voter['allowed_channel']) ?>
                                -
                                HP: <?= htmlspecialchars($voter['phone'] ?? '-') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <hr>

                <button type="submit" class="btn btn-primary">
                    Buat Request
                </button>

                <a href="<?= htmlspecialchars($appUrl) ?>/elections/<?= $election['id'] ?>/remote-verifications" class="btn btn-light">
                    Batal
                </a>
            </form>
        <?php endif; ?>
    </div>
</div>