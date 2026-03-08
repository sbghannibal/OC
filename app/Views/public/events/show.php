<?php
/** @var array<string,mixed> $event */
/** @var bool $hasAccess */
?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6">

        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                </li>
            </ol>
        </nav>

        <div class="card oc-event-card">
            <div class="card-body p-4">
                <h1 class="h3 card-title mb-3">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>
                    <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($event['is_current']): ?>
                    <span class="badge bg-primary ms-2 fs-6">Huidig</span>
                    <?php endif; ?>
                </h1>
                <dl class="row mb-4">
                    <?php if (!empty($event['starts_at'])): ?>
                    <dt class="col-5 text-muted fw-normal">Start</dt>
                    <dd class="col-7"><?= htmlspecialchars($event['starts_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($event['ends_at'])): ?>
                    <dt class="col-5 text-muted fw-normal">Einde</dt>
                    <dd class="col-7"><?= htmlspecialchars($event['ends_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                </dl>
                <?php
                $deelnemenUrl = htmlspecialchars(
                    $basePath . '/events/' . rawurlencode($event['slug']) . '/deelnemen',
                    ENT_QUOTES,
                    'UTF-8'
                );
                ?>
                <a href="<?= $deelnemenUrl ?>" class="btn btn-primary">
                    <i class="bi bi-pencil-square me-1"></i><?= $hasAccess ? 'Inschrijven' : 'Deelnemen' ?>
                </a>
            </div>
        </div>

    </div>
</div>
