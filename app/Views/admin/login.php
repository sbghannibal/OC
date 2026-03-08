<?php if ($error !== null): ?>
<div class="alert alert-danger"><i class="bi bi-x-circle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="oc-auth-wrap">
    <div class="card oc-auth-card">
        <div class="oc-card-header">
            <h1><i class="bi bi-shield-lock me-2"></i>Admin inloggen</h1>
        </div>
        <div class="card-body">
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
                <button type="submit" class="btn btn-primary w-100 mt-1">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Inloggen
                </button>
            </form>
        </div>
    </div>
</div>
