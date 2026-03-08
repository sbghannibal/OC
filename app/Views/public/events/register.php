<?php
/** @var array<string,mixed> $event */
/** @var list<array<string,mixed>> $classes */
/** @var list<array<string,mixed>> $groups */
/** @var list<string> $errors */
/** @var array<string,mixed> $old */
$oldKlasId    = (int) ($old['klas_id'] ?? 0);
$oldItemIds   = array_map('intval', (array) ($old['items'] ?? []));
?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
        </li>
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']), ENT_QUOTES, 'UTF-8') ?>">
                <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
            </a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">Aanmelden</li>
    </ol>
</nav>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-7">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h1 class="h4 mb-0"><i class="bi bi-pencil-square me-2"></i>Aanmelden – <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            </div>
            <div class="card-body p-4">

                <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <form method="post"
                      action="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']) . '/deelnemen', ENT_QUOTES, 'UTF-8') ?>"
                      id="regForm">
                    <?= \App\Core\Csrf::field() ?>

                    <div class="mb-3">
                        <label for="naam" class="form-label fw-semibold">Naam <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="naam" name="naam" required
                               value="<?= htmlspecialchars((string) ($old['naam'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="Voornaam Achternaam">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold">E-mailadres <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required
                               value="<?= htmlspecialchars((string) ($old['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="naam@voorbeeld.nl">
                    </div>

                    <div class="mb-3">
                        <label for="telefoon" class="form-label fw-semibold">Gsm-nummer <span class="text-danger">*</span></label>
                        <input type="tel" class="form-control" id="telefoon" name="telefoon" required
                               value="<?= htmlspecialchars((string) ($old['telefoon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="0470 12 34 56"
                               title="Belgisch gsm-nummer, bijv. 0470 12 34 56 of +32 470 12 34 56">
                        <div class="form-text">Belgisch gsm-nummer (bijv. 0470 12 34 56 of +32 470 12 34 56)</div>
                    </div>

                    <div class="mb-3">
                        <label for="klas_id" class="form-label fw-semibold">Klas <span class="text-danger">*</span></label>
                        <select class="form-select" id="klas_id" name="klas_id" required>
                            <option value="">— Kies je klas —</option>
                            <?php foreach ($classes as $cls): ?>
                            <option value="<?= (int) $cls['id'] ?>"
                                    data-grade="<?= (int) (\App\Models\OcClass::gradeFromName((string) $cls['name']) ?? 0) ?>"
                                    <?= ($oldKlasId === (int) $cls['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cls['name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($groups)): ?>
                    <!-- Option groups – visibility driven by selected class grade -->
                    <div id="optionGroups">
                    <?php foreach ($groups as $group): ?>
                    <?php
                        $gid       = (int) $group['id'];
                        $maxSelect = (int) $group['max_select'];
                        $required  = (bool) $group['is_required'];
                    ?>
                    <?php if ($maxSelect > 0): ?>
                    <div class="mb-3 option-group-block"
                         id="group-block-<?= $gid ?>"
                         data-group-id="<?= $gid ?>"
                         data-max-select="<?= $maxSelect ?>"
                         style="display:none">
                        <label class="form-label fw-semibold">
                            <?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>
                            <?php if ($required): ?><span class="text-danger">*</span><?php endif; ?>
                            <?php if ($maxSelect > 1): ?>
                            <small class="text-muted fw-normal">(max <?= $maxSelect ?>)</small>
                            <?php endif; ?>
                        </label>
                        <div class="option-items">
                        <?php foreach ($group['items'] as $item): ?>
                        <div class="form-check option-item"
                             data-min-grade="<?= (int) $item['min_grade'] ?>"
                             data-max-grade="<?= (int) $item['max_grade'] ?>"
                             style="display:none">
                            <?php if ($maxSelect === 1): ?>
                            <input class="form-check-input" type="radio"
                                   name="option_group_<?= $gid ?>[]"
                                   id="item-<?= (int) $item['id'] ?>"
                                   value="<?= (int) $item['id'] ?>"
                                   <?= in_array((int) $item['id'], $oldItemIds, true) ? 'checked' : '' ?>>
                            <?php else: ?>
                            <input class="form-check-input" type="checkbox"
                                   name="option_group_<?= $gid ?>[]"
                                   id="item-<?= (int) $item['id'] ?>"
                                   value="<?= (int) $item['id'] ?>"
                                   <?= in_array((int) $item['id'], $oldItemIds, true) ? 'checked' : '' ?>>
                            <?php endif; ?>
                            <label class="form-check-label" for="item-<?= (int) $item['id'] ?>">
                                <?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                        </div>
                        <?php if (empty($group['items'])): ?>
                        <p class="text-muted small">Geen opties beschikbaar voor jouw klas.</p>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="opmerking" class="form-label">Opmerking <span class="text-muted">(optioneel)</span></label>
                        <textarea class="form-control" id="opmerking" name="opmerking" rows="3"
                                  placeholder="Eventuele opmerkingen of vragen"><?= htmlspecialchars((string) ($old['opmerking'] ?? ''), ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <div class="d-flex gap-2 flex-wrap">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-check-lg me-1"></i>Aanmelden
                        </button>
                        <a href="<?= htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']), ENT_QUOTES, 'UTF-8') ?>"
                           class="btn btn-outline-secondary">
                            Annuleren
                        </a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<?php if (!empty($groups)): ?>
<script>
(function () {
    'use strict';

    var klasSelect = document.getElementById('klas_id');
    if (!klasSelect) return;

    function updateOptions() {
        var selected = klasSelect.options[klasSelect.selectedIndex];
        var grade = selected ? parseInt(selected.getAttribute('data-grade') || '0', 10) : 0;

        document.querySelectorAll('.option-group-block').forEach(function (block) {
            var visibleItems = 0;
            block.querySelectorAll('.option-item').forEach(function (item) {
                var minG = parseInt(item.getAttribute('data-min-grade') || '1', 10);
                var maxG = parseInt(item.getAttribute('data-max-grade') || '6', 10);
                var show = grade >= minG && grade <= maxG;
                item.style.display = show ? '' : 'none';
                if (show) visibleItems++;
                // Uncheck hidden inputs
                if (!show) {
                    item.querySelectorAll('input').forEach(function (inp) { inp.checked = false; });
                }
            });
            block.style.display = (grade > 0 && visibleItems > 0) ? '' : 'none';
        });
    }

    klasSelect.addEventListener('change', updateOptions);
    // Run on load (handles back-navigation with pre-filled class)
    updateOptions();
}());
</script>
<?php endif; ?>
