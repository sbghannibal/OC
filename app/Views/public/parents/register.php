<?php
/** @var string|null $error */
/** @var array<string,mixed> $old */
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0"><i class="bi bi-person-plus me-2"></i>Ouder registreren</h1>
            </div>
            <div class="card-body p-4">

                <?php if ($error !== null): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post"
                      action="<?= htmlspecialchars($basePath . '/ouder/registreren', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            E-mailadres <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="uw@email.be" autofocus>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label fw-semibold">
                            Wachtwoord <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password" name="password"
                               required minlength="8" placeholder="Minimaal 8 tekens">
                    </div>

                    <div class="mb-4">
                        <label for="password_confirm" class="form-label fw-semibold">
                            Wachtwoord bevestigen <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password_confirm"
                               name="password_confirm" required minlength="8"
                               placeholder="Herhaal wachtwoord">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-person-check me-1"></i>Registreren
                    </button>
                </form>

                <hr class="my-3">
                <p class="text-center mb-0 small">
                    Al een account?
                    <a href="<?= htmlspecialchars($basePath . '/ouder/login', ENT_QUOTES, 'UTF-8') ?>">Inloggen</a>
                </p>

            </div>
        </div>
    </div>
</div>
