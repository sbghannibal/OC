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
                <th>Klas</th>
                <th>Opties</th>
                <th>Opmerking</th>
                <th>Aangemeld op</th>
                <th>Betaalstatus</th>
                <th>Betaald op</th>
                <th>Betaalopmerking</th>
                <th>Actie</th>
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
                <td><?= htmlspecialchars($r['klas_name'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if (!empty($r['chosen_options'])): ?>
                    <ul class="mb-0 ps-3 small">
                        <?php foreach ($r['chosen_options'] as $opt): ?>
                        <li>
                            <em><?= htmlspecialchars($opt['group_name'], ENT_QUOTES, 'UTF-8') ?>:</em>
                            <?= htmlspecialchars($opt['item_name'], ENT_QUOTES, 'UTF-8') ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php else: ?>
                    <span class="text-muted">–</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($r['opmerking'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php
                    $statusLabel = match($r['payment_status'] ?? 'unknown') {
                        'paid'    => '<span class="badge bg-success">Betaald</span>',
                        'unpaid'  => '<span class="badge bg-danger">Niet betaald</span>',
                        default   => '<span class="badge bg-secondary">Onbekend</span>',
                    };
                    echo $statusLabel;
                    ?>
                </td>
                <td><?= htmlspecialchars($r['paid_at'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($r['payment_note'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#payModal<?= (int) $r['id'] ?>">
                        <i class="bi bi-pencil"></i>
                    </button>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php foreach ($registrations as $r): ?>
<!-- Payment modal for registration #<?= (int) $r['id'] ?> -->
<div class="modal fade" id="payModal<?= (int) $r['id'] ?>" tabindex="-1"
     aria-labelledby="payModalLabel<?= (int) $r['id'] ?>" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post"
                  action="<?= htmlspecialchars($basePath . '/admin/inschrijvingen/' . (int) $r['id'], ENT_QUOTES, 'UTF-8') ?>">
                <?= \App\Core\Csrf::field() ?>
                <div class="modal-header">
                    <h5 class="modal-title" id="payModalLabel<?= (int) $r['id'] ?>">
                        Betaling – <?= htmlspecialchars($r['naam'], ENT_QUOTES, 'UTF-8') ?>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Sluiten"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Betaalstatus</label>
                        <select name="payment_status" class="form-select">
                            <option value="unknown"<?= ($r['payment_status'] ?? 'unknown') === 'unknown' ? ' selected' : '' ?>>Onbekend</option>
                            <option value="paid"<?= ($r['payment_status'] ?? '') === 'paid' ? ' selected' : '' ?>>Betaald</option>
                            <option value="unpaid"<?= ($r['payment_status'] ?? '') === 'unpaid' ? ' selected' : '' ?>>Niet betaald</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Betaald op</label>
                        <input type="datetime-local" name="paid_at" class="form-control"
                               value="<?= htmlspecialchars(
                                   $r['paid_at'] !== null ? str_replace(' ', 'T', substr($r['paid_at'], 0, 16)) : '',
                                   ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Betaalopmerking</label>
                        <textarea name="payment_note" class="form-control" rows="2"><?= htmlspecialchars($r['payment_note'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuleren</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i>Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php endif; ?>
<?php endif; ?>
