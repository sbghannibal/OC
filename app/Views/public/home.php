<?php if ($event !== null): ?>
<div class="text-center py-4">
    <i class="bi bi-check-circle-fill text-success" style="font-size:3rem"></i>
    <h1 class="mt-3 mb-2"><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="lead text-muted">U bent succesvol ingelogd. Er zijn momenteel geen acties beschikbaar.</p>
</div>
<?php else: ?>
<div class="text-center py-4">
    <i class="bi bi-calendar-x text-muted" style="font-size:3rem"></i>
    <h1 class="mt-3 mb-2">Welkom</h1>
    <p class="lead text-muted">Er is nog geen evenement aangemaakt.</p>
    <a href="<?= htmlspecialchars($basePath . '/admin', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
        <i class="bi bi-arrow-right-circle me-1"></i>Ga naar het admin dashboard
    </a>
</div>
<?php endif; ?>