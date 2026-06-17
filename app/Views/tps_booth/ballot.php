<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="mb-4 text-center">
    <h1 class="h2 mb-1">Silakan Pilih Kandidat</h1>
    <div class="text-muted fs-5">
        <?= htmlspecialchars($token['election_title']) ?>
    </div>
</div>

<div class="alert alert-warning fs-5">
    <strong>Perhatian:</strong>
    Pastikan pilihan Anda benar. Setelah menekan tombol <strong>Simpan Suara</strong>,
    suara tidak dapat diubah.
</div>

<div class="card mb-4 shadow-sm">
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-4 mb-2">
                <div class="text-muted small">Kode Pemilih</div>
                <strong class="fs-5"><?= htmlspecialchars($token['voter_code']) ?></strong>
            </div>

            <div class="col-md-4 mb-2">
                <div class="text-muted small">Nama Pemilih</div>
                <strong class="fs-5"><?= htmlspecialchars($token['voter_name']) ?></strong>
            </div>

            <div class="col-md-4 mb-2">
                <div class="text-muted small">RT/RW</div>
                <strong class="fs-5"><?= htmlspecialchars(($token['rt'] ?: '-') . ' / ' . ($token['rw'] ?: '-')) ?></strong>
            </div>
        </div>
    </div>
</div>

<form 
    method="post" 
    action="<?= htmlspecialchars($appUrl) ?>/tps-booth/submit"
    onsubmit="return confirmVote();"
>
    <?= Csrf::field() ?>

    <div class="row">
        <?php foreach ($candidates as $candidate): ?>
            <div class="col-md-6 col-lg-4 mb-4">
                <label class="card h-100 shadow-sm candidate-card" style="cursor: pointer;">
                    <div class="card-body text-center">
                        <input 
                            type="radio" 
                            name="candidate_id" 
                            value="<?= htmlspecialchars((string) $candidate['id']) ?>"
                            class="form-check-input mb-3 candidate-radio"
                            required
                        >

                        <div class="mb-3">
                            <span class="badge bg-dark candidate-number">
                                No. <?= htmlspecialchars((string) $candidate['number_order']) ?>
                            </span>
                        </div>

                        <?php if (!empty($candidate['photo'])): ?>
                            <img 
                                src="<?= htmlspecialchars($appUrl . '/' . $candidate['photo']) ?>" 
                                alt="Foto kandidat"
                                class="rounded border mb-3"
                                style="width: 180px; height: 180px; object-fit: cover;"
                            >
                        <?php else: ?>
                            <div 
                                class="bg-light border rounded d-flex align-items-center justify-content-center text-muted mx-auto mb-3"
                                style="width: 180px; height: 180px;"
                            >
                                No Foto
                            </div>
                        <?php endif; ?>

                        <div class="candidate-name mb-2">
                            <?= htmlspecialchars($candidate['name']) ?>
                        </div>

                        <?php if (!empty($candidate['vision'])): ?>
                            <div class="text-start small mt-3">
                                <strong>Visi:</strong>
                                <div><?= nl2br(htmlspecialchars($candidate['vision'])) ?></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </label>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="card mt-3 shadow-sm">
        <div class="card-body d-flex justify-content-between align-items-center flex-wrap gap-2">
            <a href="<?= htmlspecialchars($appUrl) ?>/tps-booth" class="btn btn-secondary btn-lg">
                Batal
            </a>

            <button type="submit" class="btn btn-success btn-submit-vote">
                Simpan Suara
            </button>
        </div>
    </div>
</form>

<script>
function confirmVote() {
    return confirm('Yakin dengan pilihan Anda? Setelah disimpan, suara tidak bisa diubah.');
}
</script>