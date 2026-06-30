<?php

use App\Core\Csrf;

?>

<div class="card shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-3">Login Panitia</h5>

        <form method="post" action="<?= htmlspecialchars(\App\Core\Env::get('APP_URL')) ?>/login">
            <?= Csrf::field() ?>

            <div class="mb-3">
                <label class="text-muted small font-weight-bold">Username</label>
                <input 
                    type="text" 
                    name="username" 
                    class="form-control" 
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <div class="mb-3">
                <label class="text-muted small font-weight-bold">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control" 
                    autocomplete="current-password"
                    required
                >
            </div>

            <button type="submit" class="btn btn-primary w-100">
                Masuk
            </button>
        </form>
    </div>
</div>