<?php
use App\Core\Csrf;
?>
<!doctype html>
<html lang="id">
<?php include BASE_PATH .'/app/views/layouts/partials/head.php'; ?>
<body>

<?php include BASE_PATH .'/app/views/layouts/partials/navbar.php'; ?>

<div class="app-shell">
    <?php include BASE_PATH .'/app/views/layouts/partials/aside.php'; ?>

    <main class="app-content">
        <?php if ($error): ?>
            <div class="flash-wrapper">
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="flash-wrapper">
                <div class="alert alert-success">
                    <?= htmlspecialchars($success) ?>
                </div>
            </div>
        <?php endif; ?>

        <?= $content ?>
    </main>
</div>

<?php include BASE_PATH .'/app/views/layouts/partials/footer.php'; ?>

</body>
</html>