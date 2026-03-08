<?php
/** @var list<array<string,mixed>> $events */
?>
<div class="oc-hero text-center py-5 mb-4">
    <i class="bi bi-calendar-event mb-3 d-block" style="font-size:3.5rem; color: var(--oc-accent)"></i>
    <h1 class="display-5 fw-bold mb-3 oc-hero-title">OC Evenementen</h1>
    <p class="lead text-muted col-md-7 mx-auto">
        Bekijk onze evenementen en meld je aan om deel te nemen.
    </p>
</div>

<?php if (empty($events)): ?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="card oc-empty-card text-center">
            <div class="card-body py-4 px-4">
                <i class="bi bi-inbox text-secondary mb-3 d-block" style="font-size:2.5rem"></i>
                <h5 class="card-title">Nog geen evenementen beschikbaar</h5>
                <p class="card-text text-muted">Kom later terug voor aankomende evenementen.</p>
            </div>
        </div>
    </div>
</div>
<?php else: ?>
<div class="row g-4">
    <?php foreach ($events as $e): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 shadow-sm <?= $e['is_current'] ? 'border-primary' : '' ?>">
            <?php if ($e['is_current']): ?>
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-star-fill me-1"></i>Huidig evenement
            </div>
            <?php endif; ?>
            <div class="card-body d-flex flex-column">
                <h5 class="card-title"><?= htmlspecialchars($e['name'], ENT_QUOTES, 'UTF-8') ?></h5>
                <dl class="row flex-grow-1 mb-3">
                    <?php if (!empty($e['starts_at'])): ?>
                    <dt class="col-5 text-muted fw-normal">Start</dt>
                    <dd class="col-7"><?= htmlspecialchars($e['starts_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($e['ends_at'])): ?>
                    <dt class="col-5 text-muted fw-normal">Einde</dt>
                    <dd class="col-7"><?= htmlspecialchars($e['ends_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                </dl>
                <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($e['slug']), ENT_QUOTES, 'UTF-8') ?>"
                   class="btn <?= $e['is_current'] ? 'btn-primary' : 'btn-outline-primary' ?> mt-auto">
                    <i class="bi bi-arrow-right-circle me-1"></i>Meer informatie
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
