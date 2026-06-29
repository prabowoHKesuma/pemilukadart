<?php

use App\Core\ViewFormatter as F;

?>

<div class="container py-4">
    <div class="card mx-auto" style="max-width: 640px;">
        <div class="card-body text-center">
            <h1 class="h4 text-danger">Link Tidak Valid</h1>

            <p class="mb-0">
                <?= F::dash($message ?? 'Link upload tidak valid.') ?>
            </p>
        </div>
    </div>
</div>