<?php
/** @var array<string,mixed>|null $event */
/** @var list<array<string,mixed>> $registrations */
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h1 class="mb-0">Inschrijvingen</h1>
    <?php if ($event !== null && !empty($registrations)): ?>
    <a href="<?= htmlspecialchars($basePath . '/admin/inschrijvingen.csv', ENT_QUOTES, 'UTF-8') ?>"
       class="btn btn-outline-success">
        <i class="bi bi-download me-1"></i>Exporteer CSV
    </a>
    <?php endif; ?>
</div>

<?php if ($event === null): ?>
<div class="alert alert-warning">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>Er is momenteel geen actief evenement. Stel een huidig evenement in via
    <a href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>.
</div>
<?php else: ?>

<div class="alert alert-info mb-4">
    <i class="bi bi-calendar-event me-2"></i>
    Inschrijvingen voor: <strong><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></strong>
    &mdash; <strong><?= count($registrations) ?></strong> inschrijving<?= count($registrations) !== 1 ? 'en' : '' ?>
</div>

<?php if (empty($registrations)): ?>
<p class="text-muted">Nog geen inschrijvingen voor dit evenement.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Naam</th>
                <th>E-mail</th>
                <th>Telefoon</th>
                <th>Opmerking</th>
                <th>Aangemeld op</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($registrations as $r): ?>
            <tr>
                <td class="text-muted"><?= (int) $r['id'] ?></td>
                <td><?= htmlspecialchars($r['naam'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="mailto:<?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?>">
                        <?= htmlspecialchars($r['email'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </td>
                <td><?= htmlspecialchars($r['telefoon'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['opmerking'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
<?php endif; ?>
