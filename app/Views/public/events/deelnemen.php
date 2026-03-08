<?php
/** @var array<string,mixed> $event */
/** @var list<string> $errors */
/** @var array<string,string> $old */
/** @var bool $success */
?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
                </li>
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']), ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Deelnemen</li>
            </ol>
        </nav>

        <?php if ($success): ?>

        <div class="alert alert-success" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <strong>Inschrijving ontvangen!</strong> Je bent succesvol aangemeld voor
            <strong><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></strong>.
        </div>

        <?php else: ?>

        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-primary text-white">
                <h1 class="h5 mb-0">
                    <i class="bi bi-pencil-square me-2"></i>Deelnemen aan
                    <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                </h1>
            </div>
            <div class="card-body p-4">
                <form method="post"
                      action="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']) . '/deelnemen', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="naam" class="form-label">Naam <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="naam" name="naam" required
                               value="<?= htmlspecialchars($old['naam'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">E-mailadres <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="mb-3">
                        <label for="telefoon" class="form-label">Telefoonnummer</label>
                        <input type="tel" class="form-control" id="telefoon" name="telefoon"
                               value="<?= htmlspecialchars($old['telefoon'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="mb-4">
                        <label for="opmerking" class="form-label">Opmerking</label>
                        <textarea class="form-control" id="opmerking" name="opmerking" rows="3"><?= htmlspecialchars($old['opmerking'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-send me-2"></i>Inschrijven
                    </button>
                </form>
            </div>
        </div>

        <?php endif; ?>

    </div>
</div>
