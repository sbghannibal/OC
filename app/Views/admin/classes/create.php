<?php
/** @var list<string> $errors */
/** @var array<string,string> $old */
?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/admin/klassen', ENT_QUOTES, 'UTF-8') ?>">Klassen</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Nieuwe klas</li>
    </ol>
</nav>

<div class="row">
    <div class="col-md-6 col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h5 mb-0"><i class="bi bi-plus-circle me-2"></i>Nieuwe klas toevoegen</h1>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="post"
                      action="<?= htmlspecialchars($basePath . '/admin/klassen', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold">
                            Naam <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="name" name="name"
                               value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="bijv. 3A"
                               maxlength="20"
                               required>
                        <div class="form-text">Jaarcijfer (1-6) gevolgd door een letter, bijv. <code>3A</code>.</div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i>Opslaan
                        </button>
                        <a href="<?= htmlspecialchars($basePath . '/admin/klassen', ENT_QUOTES, 'UTF-8') ?>"
                           class="btn btn-outline-secondary">
                            Annuleren
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
