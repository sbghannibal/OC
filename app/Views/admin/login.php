<?php if ($error !== null): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-sm-8 col-md-4">
        <h1 class="h4 mb-4">Admin inloggen</h1>
        <form method="post" action="<?= htmlspecialchars($basePath . '/admin/login', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label for="username" class="form-label">Gebruikersnaam</label>
                <input type="text" class="form-control" id="username" name="username" autofocus required autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord</label>
                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary w-100">Inloggen</button>
        </form>
    </div>
</div>
