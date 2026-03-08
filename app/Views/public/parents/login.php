<?php
/** @var string|null $error */
/** @var string $return */
?>
<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0"><i class="bi bi-person-lock me-2"></i>Ouder inloggen</h1>
            </div>
            <div class="card-body p-4">

                <?php if ($error !== null): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>

                <form method="post"
                      action="<?= htmlspecialchars($basePath . '/ouder/login', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>
                    <?php if ($return !== ''): ?>
                    <input type="hidden" name="return"
                           value="<?= htmlspecialchars($return, ENT_QUOTES, 'UTF-8') ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">
                            E-mailadres <span class="text-danger">*</span>
                        </label>
                        <input type="email" class="form-control" id="email" name="email"
                               required placeholder="uw@email.be" autofocus>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label fw-semibold">
                            Wachtwoord <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control" id="password"
                               name="password" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Inloggen
                    </button>
                </form>

                <hr class="my-3">
                <p class="text-center mb-0 small">
                    Nog geen account?
                    <a href="<?= htmlspecialchars($basePath . '/ouder/registreren', ENT_QUOTES, 'UTF-8') ?>">Registreren</a>
                </p>

            </div>
        </div>
    </div>
</div>
