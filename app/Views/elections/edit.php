<?php

use App\Core\Csrf;
use App\Core\Env;

function formatDateTimeLocal(?string $dateTime): string
{
    if (!$dateTime) {
        return '';
    }

    return date('Y-m-d\TH:i', strtotime($dateTime));
}

?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h1 class="h3 mb-0">Edit Pemilihan</h1>
        <div class="text-muted small">
            Perbarui data sesi pemilihan.
        </div>
    </div>

    <a href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections" class="btn btn-secondary">
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-body">
        <form method="post" action="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections/<?= $election['id'] ?>/update">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label class="form-label">Nama Pemilihan <span class="text-danger">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    class="form-control" 
                    value="<?= htmlspecialchars($election['title']) ?>"
                    required
                    autofocus
                >
            </div>

            <div class="mb-3">
                <label class="form-label">Deskripsi</label>
                <textarea 
                    name="description" 
                    class="form-control" 
                    rows="3"
                ><?= htmlspecialchars($election['description'] ?? '') ?></textarea>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="draft" <?= $election['status'] === 'draft' ? 'selected' : '' ?>>Draft</option>
                        <option value="open" <?= $election['status'] === 'open' ? 'selected' : '' ?>>Open</option>
                        <option value="closed" <?= $election['status'] === 'closed' ? 'selected' : '' ?>>Closed</option>
                        <option value="finished" <?= $election['status'] === 'finished' ? 'selected' : '' ?>>Finished</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Mulai</label>
                    <input 
                        type="datetime-local" 
                        name="start_at" 
                        class="form-control"
                        value="<?= htmlspecialchars(formatDateTimeLocal($election['start_at'])) ?>"
                    >
                </div>

                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Selesai</label>
                    <input 
                        type="datetime-local" 
                        name="end_at" 
                        class="form-control"
                        value="<?= htmlspecialchars(formatDateTimeLocal($election['end_at'])) ?>"
                    >
                </div>
            </div>

            <hr>

            <button type="submit" class="btn btn-primary">
                Update
            </button>

            <a href="<?= htmlspecialchars(Env::get('APP_URL')) ?>/elections" class="btn btn-light">
                Batal
            </a>
        </form>
    </div>
</div>