<?php
/** @var array<string,mixed> $event */
/** @var list<array{registration: array<string,mixed>, child: array<string,mixed>|null, options: list<array<string,mixed>>, subtotal: float}> $regRows */
/** @var float $eventTotal */
/** @var bool $isOpen */
$slug         = $event['slug'];
$deelnemenUrl = htmlspecialchars($basePath . '/events/' . rawurlencode($slug) . '/deelnemen', ENT_QUOTES, 'UTF-8');
$afmeldenBase = $basePath . '/events/' . rawurlencode($slug) . '/afmelden';

/**
 * Format a monetary amount in Belgian/Dutch style.
 */
$formatPrice = static function (float $amount): string {
    return '€ ' . number_format($amount, 2, ',', '.');
};
?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($slug), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Mijn inschrijvingen</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-lg-9">

        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <h1 class="mb-0 h3">
                <i class="bi bi-list-check me-2 text-primary"></i>Mijn inschrijvingen
            </h1>
            <span class="badge bg-secondary fs-6"><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></span>
        </div>

        <?php if (!$isOpen): ?>
        <div class="alert alert-warning">
            <i class="bi bi-lock-fill me-2"></i>
            Inschrijvingen zijn gesloten. Wijzigen en afmelden is niet meer mogelijk.
        </div>
        <?php endif; ?>

        <?php if (empty($regRows)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            U heeft nog geen kinderen ingeschreven voor dit evenement.
        </div>
        <?php else: ?>

        <?php foreach ($regRows as $row): ?>
        <?php
            $reg      = $row['registration'];
            $child    = $row['child'];
            $options  = $row['options'];
            $subtotal = (float) $row['subtotal'];
            $childName = $child !== null
                ? htmlspecialchars(
                    $child['first_name'] . (!empty($child['last_name']) ? ' ' . $child['last_name'] : ''),
                    ENT_QUOTES, 'UTF-8'
                  )
                : htmlspecialchars($reg['naam'] ?? '–', ENT_QUOTES, 'UTF-8');
            $childKlas = ($child !== null && !empty($child['klas_name']))
                ? htmlspecialchars((string) $child['klas_name'], ENT_QUOTES, 'UTF-8')
                : null;
        ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <i class="bi bi-person-fill me-1 text-primary"></i>
                    <strong><?= $childName ?></strong>
                    <?php if ($childKlas !== null): ?>
                    <span class="badge bg-light text-dark ms-1"><?= $childKlas ?></span>
                    <?php endif; ?>
                </div>
                <?php if ($isOpen): ?>
                <div class="d-flex gap-2">
                    <?php $editUrl = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen'
                        . ($child !== null ? '?kind=' . (int) $child['id'] : ''); ?>
                    <a href="<?= htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8') ?>"
                       class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-pencil me-1"></i>Inschrijving wijzigen
                    </a>
                    <form method="post"
                          action="<?= htmlspecialchars($afmeldenBase, ENT_QUOTES, 'UTF-8') ?>">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="registration_id" value="<?= (int) $reg['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger oc-afmelden-btn"
                                data-child-name="<?= $childName ?>">
                            <i class="bi bi-x-circle me-1"></i>Afmelden
                        </button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($options)): ?>
                <table class="table table-sm mb-2">
                    <thead class="table-light">
                        <tr>
                            <th>Groep</th>
                            <th>Keuze</th>
                            <th class="text-end">Prijs</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($options as $opt): ?>
                        <tr>
                            <td class="text-muted small"><?= htmlspecialchars($opt['group_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td><?= htmlspecialchars($opt['item_name'], ENT_QUOTES, 'UTF-8') ?></td>
                            <td class="text-end"><?= $formatPrice((float) $opt['price']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="fw-semibold">
                            <td colspan="2" class="text-end">Subtotaal</td>
                            <td class="text-end"><?= $formatPrice($subtotal) ?></td>
                        </tr>
                    </tfoot>
                </table>
                <?php else: ?>
                <p class="text-muted mb-0 small"><i class="bi bi-dash-circle me-1"></i>Geen keuzes gemaakt.</p>
                <?php endif; ?>

                <?php if (!empty($reg['opmerking'])): ?>
                <p class="small text-muted mt-2 mb-0">
                    <i class="bi bi-chat-left-text me-1"></i>
                    <em><?= htmlspecialchars((string) $reg['opmerking'], ENT_QUOTES, 'UTF-8') ?></em>
                </p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>

        <div class="card border-primary mb-4">
            <div class="card-body d-flex justify-content-between align-items-center">
                <strong class="fs-5">Totaal voor dit evenement</strong>
                <span class="fs-5 fw-bold text-primary"><?= $formatPrice($eventTotal) ?></span>
            </div>
        </div>

        <?php endif; ?>

        <?php if ($isOpen): ?>
        <div class="d-flex gap-2 flex-wrap">
            <a href="<?= $deelnemenUrl ?>" class="btn btn-primary">
                <i class="bi bi-person-plus me-1"></i>Nog een kind inschrijven
            </a>
            <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($slug), ENT_QUOTES, 'UTF-8') ?>"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Terug naar evenement
            </a>
        </div>
        <?php else: ?>
        <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($slug), ENT_QUOTES, 'UTF-8') ?>"
           class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>Terug naar evenement
        </a>
        <?php endif; ?>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.oc-afmelden-btn').forEach(function (btn) {
        btn.closest('form').addEventListener('submit', function (e) {
            var name = btn.dataset.childName || '';
            if (!confirm('Weet u zeker dat u ' + name + ' wilt afmelden?')) {
                e.preventDefault();
            }
        });
    });
});
</script>
