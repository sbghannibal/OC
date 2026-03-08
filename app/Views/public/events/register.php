<?php
/** @var array<string,mixed> $event */
/** @var list<array<string,mixed>> $classes */
/** @var list<array<string,mixed>> $groups */
/** @var list<array<string,mixed>> $children */
/** @var list<string> $errors */
/** @var array<string,mixed> $old */
/** @var array<string,mixed>|null $duplicateChild */
/** @var array<string,mixed> $pendingChildData */
$oldKlasId    = (int) ($old['klas_id'] ?? 0);
$oldItemIds   = array_map('intval', (array) ($old['items'] ?? []));
$oldChildSel  = (string) ($old['child_select'] ?? '');
$formAction   = htmlspecialchars($basePath . '/events/' . rawurlencode($event['slug']) . '/deelnemen', ENT_QUOTES, 'UTF-8');
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

                <?php if ($duplicateChild !== null): ?>
                <!-- ── Duplicate child confirmation ──────────────────────── -->
                <div class="alert alert-warning">
                    <h5 class="alert-heading"><i class="bi bi-exclamation-triangle me-2"></i>Mogelijk bestaand kind gevonden</h5>
                    <p class="mb-2">
                        We hebben een kind met een gelijkaardige naam gevonden in uw account:
                        <strong><?= htmlspecialchars((string) $duplicateChild['first_name'] . (!empty($duplicateChild['last_name']) ? ' ' . $duplicateChild['last_name'] : ''), ENT_QUOTES, 'UTF-8') ?></strong>
                        <?php if (!empty($duplicateChild['klas_name'])): ?>
                        (<?= htmlspecialchars((string) $duplicateChild['klas_name'], ENT_QUOTES, 'UTF-8') ?>)
                        <?php endif; ?>
                    </p>
                    <p class="mb-3">Wat wilt u doen?</p>
                    <div class="d-flex gap-2 flex-wrap">
                        <!-- Use existing child -->
                        <form method="post" action="<?= $formAction ?>" class="d-inline">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="child_select"     value="new">
                            <input type="hidden" name="child_action"     value="use_existing">
                            <input type="hidden" name="confirm_child_id" value="<?= (int) $duplicateChild['id'] ?>">
                            <input type="hidden" name="child_first_name" value="<?= htmlspecialchars((string) ($pendingChildData['first_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="child_last_name"  value="<?= htmlspecialchars((string) ($pendingChildData['last_name']  ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="child_birthdate"  value="<?= htmlspecialchars((string) ($pendingChildData['birthdate']   ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="klas_id"          value="<?= (int) ($pendingChildData['klas_id'] ?? 0) ?>">
                            <input type="hidden" name="telefoon"         value="<?= htmlspecialchars((string) ($old['telefoon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="opmerking"        value="<?= htmlspecialchars((string) ($old['opmerking'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ($oldItemIds as $iid): ?>
                            <input type="hidden" name="option_group_restore[]" value="<?= (int) $iid ?>">
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-person-check me-1"></i>Bestaand kind gebruiken
                            </button>
                        </form>
                        <!-- Create new child anyway -->
                        <form method="post" action="<?= $formAction ?>" class="d-inline">
                            <?= \App\Core\Csrf::field() ?>
                            <input type="hidden" name="child_select"    value="new">
                            <input type="hidden" name="child_action"    value="create_new">
                            <input type="hidden" name="child_first_name" value="<?= htmlspecialchars((string) ($pendingChildData['first_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="child_last_name"  value="<?= htmlspecialchars((string) ($pendingChildData['last_name']  ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="child_birthdate"  value="<?= htmlspecialchars((string) ($pendingChildData['birthdate']   ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="klas_id"          value="<?= (int) ($pendingChildData['klas_id'] ?? 0) ?>">
                            <input type="hidden" name="telefoon"         value="<?= htmlspecialchars((string) ($old['telefoon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="opmerking"        value="<?= htmlspecialchars((string) ($old['opmerking'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            <?php foreach ($oldItemIds as $iid): ?>
                            <input type="hidden" name="option_group_restore[]" value="<?= (int) $iid ?>">
                            <?php endforeach; ?>
                            <button type="submit" class="btn btn-outline-secondary">
                                <i class="bi bi-person-plus me-1"></i>Toch nieuw kind aanmaken
                            </button>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <!-- ── Main registration form ────────────────────────────── -->
                <form method="post" action="<?= $formAction ?>" id="regForm">
                    <?= \App\Core\Csrf::field() ?>

                    <!-- ── Child selection ──────────────────────────────── -->
                    <fieldset class="mb-4">
                        <legend class="fw-semibold fs-6 mb-2">
                            <i class="bi bi-people me-1"></i>Kind <span class="text-danger">*</span>
                        </legend>

                        <?php foreach ($children as $child): ?>
                        <?php $childVal = 'existing_' . (int) $child['id']; ?>
                        <div class="form-check">
                            <input class="form-check-input child-radio" type="radio"
                                   name="child_select" id="child-<?= (int) $child['id'] ?>"
                                   value="<?= htmlspecialchars($childVal, ENT_QUOTES, 'UTF-8') ?>"
                                   <?= ($oldChildSel === $childVal) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="child-<?= (int) $child['id'] ?>">
                                <strong><?= htmlspecialchars((string) $child['first_name'] . (!empty($child['last_name']) ? ' ' . $child['last_name'] : ''), ENT_QUOTES, 'UTF-8') ?></strong>
                                <?php if (!empty($child['klas_name'])): ?>
                                <span class="text-muted small">(<?= htmlspecialchars((string) $child['klas_name'], ENT_QUOTES, 'UTF-8') ?>)</span>
                                <?php endif; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>

                        <div class="form-check mt-1">
                            <input class="form-check-input child-radio" type="radio"
                                   name="child_select" id="child-new" value="new"
                                   <?= ($oldChildSel === 'new' || empty($children)) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="child-new">
                                <i class="bi bi-person-plus me-1"></i><strong>Nieuw kind toevoegen</strong>
                            </label>
                        </div>
                    </fieldset>

                    <!-- ── New child fields ──────────────────────────────── -->
                    <div id="newChildFields" class="border rounded p-3 mb-4 bg-light"
                         <?= ($oldChildSel !== 'new' && !empty($children)) ? 'style="display:none"' : '' ?>>
                        <p class="fw-semibold mb-3"><i class="bi bi-person-vcard me-1"></i>Gegevens nieuw kind</p>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <label for="child_first_name" class="form-label">
                                    Voornaam <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="child_first_name"
                                       name="child_first_name"
                                       value="<?= htmlspecialchars((string) ($pendingChildData['first_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Voornaam">
                            </div>
                            <div class="col-sm-6">
                                <label for="child_last_name" class="form-label">Achternaam</label>
                                <input type="text" class="form-control" id="child_last_name"
                                       name="child_last_name"
                                       value="<?= htmlspecialchars((string) ($pendingChildData['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                       placeholder="Achternaam (optioneel)">
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label for="child_birthdate" class="form-label">
                                    Geboortedatum <span class="text-muted">(optioneel)</span>
                                </label>
                                <input type="date" class="form-control" id="child_birthdate"
                                       name="child_birthdate"
                                       value="<?= htmlspecialchars((string) ($pendingChildData['birthdate'] ?? ''), ENT_QUOTES, 'UTF-8') ?>">
                            </div>
                            <div class="col-sm-6">
                                <label for="klas_id" class="form-label">
                                    Klas <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="klas_id" name="klas_id">
                                    <option value="">— Kies klas —</option>
                                    <?php foreach ($classes as $cls): ?>
                                    <?php
                                        $cSelected = (
                                            (int) ($pendingChildData['klas_id'] ?? 0) === (int) $cls['id'] ||
                                            ($oldKlasId === (int) $cls['id'] && $oldChildSel === 'new')
                                        );
                                    ?>
                                    <option value="<?= (int) $cls['id'] ?>"
                                            data-grade="<?= (int) (\App\Models\OcClass::gradeFromName((string) $cls['name']) ?? 0) ?>"
                                            <?= $cSelected ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cls['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
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

                    <div class="mb-3">
                        <label for="telefoon" class="form-label fw-semibold">
                            Gsm-nummer <span class="text-danger">*</span>
                        </label>
                        <input type="tel" class="form-control" id="telefoon" name="telefoon" required
                               value="<?= htmlspecialchars((string) ($old['telefoon'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                               placeholder="0470 12 34 56"
                               title="Belgisch gsm-nummer, bijv. 0470 12 34 56 of +32 470 12 34 56">
                        <div class="form-text">Belgisch gsm-nummer (bijv. 0470 12 34 56 of +32 470 12 34 56)</div>
                    </div>

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
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    // ── Child selection toggle ────────────────────────────────────────────────
    var newChildFields = document.getElementById('newChildFields');
    var childRadios    = document.querySelectorAll('.child-radio');

    function isNewChildSelected() {
        var r = document.querySelector('input[name="child_select"][value="new"]');
        return r ? r.checked : false;
    }

    function updateChildFieldsVisibility() {
        if (!newChildFields) return;
        newChildFields.style.display = isNewChildSelected() ? '' : 'none';
        var fName = document.getElementById('child_first_name');
        var klas  = document.getElementById('klas_id');
        if (fName) fName.required = isNewChildSelected();
        if (klas)  klas.required  = isNewChildSelected();
    }

    childRadios.forEach(function (r) {
        r.addEventListener('change', function () {
            updateChildFieldsVisibility();
            updateOptions();
        });
    });
    updateChildFieldsVisibility();

    // ── Option group visibility ───────────────────────────────────────────────
    var klasSelect = document.getElementById('klas_id');

    function getSelectedGrade() {
        var checked = document.querySelector('input[name="child_select"]:checked');
        if (!checked) return 0;
        if (checked.value === 'new') {
            if (!klasSelect) return 0;
            var opt = klasSelect.options[klasSelect.selectedIndex];
            return opt ? parseInt(opt.getAttribute('data-grade') || '0', 10) : 0;
        }
        return parseInt(checked.getAttribute('data-grade') || '0', 10);
    }

    function updateOptions() {
        var grade = getSelectedGrade();
        document.querySelectorAll('.option-group-block').forEach(function (block) {
            var visibleItems = 0;
            block.querySelectorAll('.option-item').forEach(function (item) {
                var minG = parseInt(item.getAttribute('data-min-grade') || '1', 10);
                var maxG = parseInt(item.getAttribute('data-max-grade') || '6', 10);
                var show = grade >= minG && grade <= maxG;
                item.style.display = show ? '' : 'none';
                if (show) visibleItems++;
                if (!show) {
                    item.querySelectorAll('input').forEach(function (inp) { inp.checked = false; });
                }
            });
            block.style.display = (grade > 0 && visibleItems > 0) ? '' : 'none';
        });
    }

    if (klasSelect) {
        klasSelect.addEventListener('change', updateOptions);
    }

    // Embed grade data on existing-child radio buttons
    <?php foreach ($children as $child): ?>
    <?php
        $cKlasId  = (int) ($child['klas_id'] ?? 0);
        $cKlasRow = null;
        foreach ($classes as $cls) {
            if ((int) $cls['id'] === $cKlasId) { $cKlasRow = $cls; break; }
        }
        $cGrade = ($cKlasRow !== null) ? (\App\Models\OcClass::gradeFromName((string) $cKlasRow['name']) ?? 0) : 0;
    ?>
    (function() {
        var r = document.getElementById('child-<?= (int) $child['id'] ?>');
        if (r) r.setAttribute('data-grade', '<?= (int) $cGrade ?>');
    }());
    <?php endforeach; ?>

    updateOptions();
}());
</script>
