<?php
/** @var array<string,mixed> $event */
/** @var bool $success */
?>
<?php if ($success): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <i class="bi bi-check-circle-fill me-2"></i>
    <strong>Inschrijving ontvangen!</strong> Bedankt voor je aanmelding voor <strong><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></strong>.
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
</div>
<?php endif; ?>

<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
        </li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <?php if ($event['is_current']): ?>
        <span class="badge bg-primary mb-3"><i class="bi bi-star-fill me-1"></i>Huidig evenement</span>
        <?php endif; ?>

        <h1 class="mb-4"><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></h1>

        <div class="card oc-event-card mb-4">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">
                    <i class="bi bi-info-circle me-2 text-primary"></i>Evenement details
                </h5>
                <dl class="row mb-0">
                    <?php if (!empty($event['starts_at'])): ?>
                    <dt class="col-sm-4 text-muted fw-normal">Startdatum</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($event['starts_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($event['ends_at'])): ?>
                    <dt class="col-sm-4 text-muted fw-normal">Einddatum</dt>
                    <dd class="col-sm-8"><?= htmlspecialchars($event['ends_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']) . '/deelnemen', ENT_QUOTES, 'UTF-8') ?>"
               class="btn btn-primary btn-lg px-4">
                <i class="bi bi-pencil-square me-2"></i>Aanmelden voor dit evenement
            </a>
            <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Terug naar overzicht
            </a>
        </div>
    </div>
</div>
