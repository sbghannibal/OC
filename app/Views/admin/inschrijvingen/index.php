<?php
/** @var array<string,mixed>|null $event */
/** @var list<array<string,mixed>> $registrations */
/** @var string|null $qrUrl */
/** @var list<string> $statuses */

$statusLabel = static function (string $s): string {
    return match ($s) {
        'paid'    => '<span class="badge bg-success">Betaald</span>',
        'unpaid'  => '<span class="badge bg-danger">Niet betaald</span>',
        default   => '<span class="badge bg-secondary">Onbekend</span>',
    };
};
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="mb-0">Inschrijvingen</h1>
    <?php if ($event !== null): ?>
    <a href="<?= htmlspecialchars($basePath . '/admin/inschrijvingen.csv', ENT_QUOTES, 'UTF-8') ?>"
       class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-download me-1"></i>CSV exporteren
    </a>
    <?php endif; ?>
</div>

<?php if ($event === null): ?>
<div class="alert alert-warning">Er is geen huidig evenement. Maak eerst een evenement aan.</div>
<?php else: ?>

<div class="mb-3">
    <strong>Evenement:</strong> <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
    <code class="ms-2"><?= htmlspecialchars($event['slug'], ENT_QUOTES, 'UTF-8') ?></code>
</div>

<?php if ($qrUrl !== null): ?>
<div class="card mb-4 border-info">
    <div class="card-header bg-info text-white py-2">
        <i class="bi bi-qr-code me-1"></i> QR-registratielink (geldig 7 dagen)
    </div>
    <div class="card-body py-2">
        <p class="mb-1 small text-muted">
            Deel deze link via QR-code. Bezoekers kunnen direct inschrijven zonder toegangscode.
        </p>
        <div class="input-group input-group-sm">
            <input type="text" class="form-control font-monospace" id="qrLinkInput"
                   readonly value="<?= htmlspecialchars($qrUrl, ENT_QUOTES, 'UTF-8') ?>">
            <button class="btn btn-outline-secondary" type="button"
                    onclick="navigator.clipboard.writeText(document.getElementById('qrLinkInput').value)">
                <i class="bi bi-clipboard"></i>
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<?php if (empty($registrations)): ?>
<p class="text-muted">Nog geen inschrijvingen voor dit evenement.</p>
<?php else: ?>

<p class="text-muted small"><?= count($registrations) ?> inschrijving(en)</p>

<div class="table-responsive">
<table class="table table-bordered table-striped table-sm align-middle">
    <thead class="table-dark">
        <tr>
            <th>#</th>
            <th>Naam</th>
            <th>E-mail</th>
            <th>Telefoon</th>
            <th>Opmerking</th>
            <th>Betaalstatus</th>
            <th>Aangemeld op</th>
            <th>Wijzigen</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($registrations as $reg): ?>
        <tr>
            <td><?= (int) $reg['id'] ?></td>
            <td><?= htmlspecialchars($reg['naam'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><a href="mailto:<?= htmlspecialchars($reg['email'], ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($reg['email'], ENT_QUOTES, 'UTF-8') ?></a></td>
            <td><?= htmlspecialchars($reg['telefoon'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($reg['opmerking'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= $statusLabel($reg['payment_status']) ?></td>
            <td class="text-nowrap small"><?= htmlspecialchars($reg['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <form method="post"
                      action="<?= htmlspecialchars($basePath . '/admin/inschrijvingen/betaalstatus', ENT_QUOTES, 'UTF-8') ?>"
                      class="d-flex gap-1">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="registration_id" value="<?= (int) $reg['id'] ?>">
                    <select name="payment_status" class="form-select form-select-sm" style="min-width:130px">
                        <?php foreach ($statuses as $s): ?>
                        <option value="<?= htmlspecialchars($s, ENT_QUOTES, 'UTF-8') ?>"
                            <?= $s === $reg['payment_status'] ? 'selected' : '' ?>>
                            <?= match ($s) {
                                'paid'   => 'Betaald',
                                'unpaid' => 'Niet betaald',
                                default  => 'Onbekend',
                            } ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap">
                        <i class="bi bi-check2"></i>
                    </button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php endif; ?>
<?php endif; ?>
