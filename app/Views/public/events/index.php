<?php
/** @var list<array<string,mixed>> $events */
?>
<h1 class="mb-4"><i class="bi bi-calendar-event me-2"></i>Evenementen</h1>

<?php if (empty($events)): ?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="card oc-empty-card text-center">
            <div class="card-body py-4 px-4">
                <i class="bi bi-inbox text-secondary mb-3 d-block" style="font-size:2.5rem"></i>
                <h5 class="card-title">Geen evenementen beschikbaar</h5>
                <p class="card-text text-muted">Er zijn momenteel geen evenementen gepland.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>

<?php foreach ($events as $event): ?>
<div class="card mb-3 <?= $event['is_current'] ? 'border-primary' : '' ?>">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <h5 class="card-title mb-1">
                    <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($event['is_current']): ?>
                    <span class="badge bg-primary ms-2">Huidig</span>
                    <?php endif; ?>
                </h5>
                <?php if (!empty($event['starts_at']) || !empty($event['ends_at'])): ?>
                <p class="text-muted mb-2 small">
                    <?php if (!empty($event['starts_at'])): ?>
                    <i class="bi bi-calendar me-1"></i><?= htmlspecialchars($event['starts_at'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                    <?php if (!empty($event['ends_at'])): ?>
                    &ndash; <?= htmlspecialchars($event['ends_at'], ENT_QUOTES, 'UTF-8') ?>
                    <?php endif; ?>
                </p>
                <?php endif; ?>
            </div>
            <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']), ENT_QUOTES, 'UTF-8') ?>"
               class="btn btn-sm btn-outline-primary ms-3 flex-shrink-0">
                <i class="bi bi-arrow-right me-1"></i>Bekijken
            </a>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
