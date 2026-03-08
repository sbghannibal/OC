<h1 class="mb-4">Nieuwe gebruiker aanmaken</h1>

<?php if (!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
                <li><?= htmlspecialchars($e, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-12 col-md-6">
        <form method="post" action="<?= htmlspecialchars($basePath . '/admin/users', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label for="username" class="form-label">Gebruikersnaam <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="username" name="username"
                       value="<?= htmlspecialchars((string) ($old['username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                       required pattern="[a-zA-Z0-9_\-\.]{3,50}" autocomplete="off">
                <div class="form-text">3–50 tekens: letters, cijfers, _, - en .</div>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Wachtwoord <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password" name="password"
                       minlength="8" required autocomplete="new-password">
                <div class="form-text">Minimaal 8 tekens.</div>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Wachtwoord bevestigen <span class="text-danger">*</span></label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                       minlength="8" required autocomplete="new-password">
            </div>
            <button type="submit" class="btn btn-primary">Gebruiker aanmaken</button>
            <a href="<?= htmlspecialchars($basePath . '/admin/users', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary ms-2">Annuleren</a>
        </form>
    </div>
</div>
