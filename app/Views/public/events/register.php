<?php
/** @var array<string,mixed> $event */
/** @var list<string> $errors */
/** @var array<string,string> $old */
?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Aanmelden</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0"><i class="bi bi-pencil-square me-2"></i>Aanmelden – <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></h1>
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
                      action="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']) . '/deelnemen', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="naam" class="form-label fw-semibold">Naam <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="naam" name="naam" required
                               value="<?= htmlspecialchars($old['naam'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Voornaam Achternaam">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mailadres <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= htmlspecialchars($old['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="naam@voorbeeld.nl">
                    </div>

                    <div class="mb-3">
                        <label for="telefoon" class="form-label">Telefoonnummer <span class="text-muted">(optioneel)</span></label>
                        <input type="tel" class="form-control" id="telefoon" name="telefoon"
                               value="<?= htmlspecialchars($old['telefoon'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="+31 6 00000000">
                    </div>

                    <div class="mb-4">
                        <label for="opmerking" class="form-label">Opmerking <span class="text-muted">(optioneel)</span></label>
                        <textarea class="form-control" id="opmerking" name="opmerking" rows="3"
                                  placeholder="Eventuele opmerkingen of vragen"><?= htmlspecialchars($old['opmerking'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Aanmelden
                        </button>
                        <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']), ENT_QUOTES, 'UTF-8') ?>"
                           class="btn btn-outline-secondary">
                            Annuleren
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
