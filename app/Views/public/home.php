<?php if ($event === null): ?>

<div class="oc-hero text-center py-5">
    <i class="bi bi-calendar-event mb-3 d-block" style="font-size:3.5rem; color: var(--oc-accent)"></i>
    <h1 class="display-5 fw-bold mb-3 oc-hero-title">Welkom bij OC Acties</h1>
    <p class="lead text-muted col-md-7 mx-auto">
        Jouw centrale platform voor het bijhouden en beheren van OC activiteiten en evenementen.
    </p>
</div>

<div class="row justify-content-center mt-2">
    <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="card oc-empty-card text-center">
            <div class="card-body py-4 px-4">
                <i class="bi bi-inbox text-secondary mb-3 d-block" style="font-size:2.5rem"></i>
                <h5 class="card-title">Nog geen evenement aangemaakt</h5>
                <p class="card-text text-muted mb-4">
                    Er zijn nog geen evenementen beschikbaar. Ga naar het admin dashboard
                    om het eerste evenement aan te maken.
                </p>
                <a href="<?= htmlspecialchars($basePath . '/admin', ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-primary px-4">
                    <i class="bi bi-plus-circle me-1"></i>Eerste evenement aanmaken
                </a>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<div class="oc-hero text-center py-5">
    <i class="bi bi-check-circle-fill text-success mb-3 d-block" style="font-size:3.5rem"></i>
    <h1 class="display-5 fw-bold mb-3 oc-hero-title"><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead text-muted col-md-7 mx-auto">
        Welkom! U bent succesvol aangemeld voor dit evenement.
    </p>
</div>

<div class="row justify-content-center">
    <div class="col-sm-10 col-md-8 col-lg-6">
        <div class="card oc-event-card">
            <div class="card-body p-4">
                <h5 class="card-title mb-3">
                    <i class="bi bi-calendar-event me-2 text-primary"></i>Evenement details
                </h5>
                <dl class="row mb-0">
                    <dt class="col-5 text-muted fw-normal">Naam</dt>
                    <dd class="col-7 fw-semibold"><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php if (!empty($event['starts_at'])): ?>
                    <dt class="col-5 text-muted fw-normal">Start</dt>
                    <dd class="col-7"><?= htmlspecialchars($event['starts_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                    <?php if (!empty($event['ends_at'])): ?>
                    <dt class="col-5 text-muted fw-normal">Einde</dt>
                    <dd class="col-7"><?= htmlspecialchars($event['ends_at'], ENT_QUOTES, 'UTF-8') ?></dd>
                    <?php endif; ?>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php endif; ?>