<?php

use App\Core\Csrf;
use App\Core\Env;

$appUrl = rtrim(Env::get('APP_URL'), '/');

?>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card mb-3 shadow-sm">
            <div class="card-body text-center">
                <h1 class="h3 mb-1">Coblos Remote</h1>
                <div class="text-muted">
                    <?= htmlspecialchars($token['election_title']) ?>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            Pastikan pilihan Anda benar. Setelah menekan tombol <strong>Simpan Suara</strong>,
            pilihan tidak bisa diubah dan token akan langsung mati.
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4 mb-2">
                        <div class="text-muted small">Kode Pemilih</div>
                        <strong><?= htmlspecialchars($token['voter_code']) ?></strong>
                    </div>

                    <div class="col-md-4 mb-2">
                        <div class="text-muted small">Nama Pemilih</div>
                        <strong><?= htmlspecialchars($token['voter_name']) ?></strong>
                    </div>

                    <div class="col-md-4 mb-2">
                        <div class="text-muted small">Token Expired</div>
                        <strong><?= date('d/m/Y H:i', strtotime($token['expires_at'])) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <form 
            method="post" 
            action="<?= htmlspecialchars($appUrl) ?>/remote-vote/<?= htmlspecialchars($plainToken) ?>/submit"
            onsubmit="return confirm('Yakin dengan pilihan Anda? Setelah disimpan, suara tidak bisa diubah.');"
        >
            <?= Csrf::field() ?>

            <div class="row">
                <?php foreach ($candidates as $candidate): ?>
                    <div class="col-md-6 col-lg-4 mb-3">
                        <label class="card h-100 shadow-sm" style="cursor: pointer;">
                            <div class="card-body text-center">
                                <input 
                                    type="radio" 
                                    name="candidate_id" 
                                    value="<?= htmlspecialchars((string) $candidate['id']) ?>"
                                    class="form-check-input mb-3"
                                    required
                                >

                                <div class="mb-2">
                                    <span class="badge bg-dark fs-5">
                                        No. <?= htmlspecialchars((string) $candidate['number_order']) ?>
                                    </span>
                                </div>

                                <?php if (!empty($candidate['photo'])): ?>
                                    <img 
                                        src="<?= htmlspecialchars($appUrl . '/' . $candidate['photo']) ?>" 
                                        alt="Foto kandidat"
                                        class="rounded border mb-3"
                                        style="width: 150px; height: 150px; object-fit: cover;"
                                    >
                                <?php else: ?>
                                    <div 
                                        class="bg-light border rounded d-flex align-items-center justify-content-center text-muted mx-auto mb-3"
                                        style="width: 150px; height: 150px;"
                                    >
                                        No Foto
                                    </div>
                                <?php endif; ?>

                                <h4 class="mb-2">
                                    <?= htmlspecialchars($candidate['name']) ?>
                                </h4>

                                <?php if (!empty($candidate['vision'])): ?>
                                    <div class="text-start small">
                                        <strong>Visi:</strong>
                                        <div><?= nl2br(htmlspecialchars($candidate['vision'])) ?></div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </label>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="card mt-3">
                <div class="card-body text-end">
                    <button type="submit" class="btn btn-success btn-lg">
                        Simpan Suara
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>