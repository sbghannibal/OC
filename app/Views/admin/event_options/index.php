<?php
/** @var array<string,mixed> $event */
/** @var list<array<string,mixed>> $groups */
/** @var list<string> $errors */
$slug = $event['slug'];
?>
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item">
            <a href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
        </li>
        <li class="breadcrumb-item active" aria-current="page">
            Opties – <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?>
        </li>
    </ol>
</nav>

<div class="d-flex align-items-center mb-4 gap-3">
    <h1 class="mb-0">Kiesopties</h1>
    <span class="badge bg-secondary fs-6"><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></span>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php foreach ($groups as $group): ?>
<?php $gid = (int) $group['id']; ?>
<div class="card mb-4 shadow-sm">
    <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <strong><?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?></strong>
            <span class="badge bg-secondary">max <?= (int) $group['max_select'] ?></span>
            <?php if ($group['is_required']): ?>
            <span class="badge bg-warning text-dark">verplicht</span>
            <?php endif; ?>
        </div>
        <div class="d-flex gap-1">
            <!-- Edit group button -->
            <button type="button" class="btn btn-sm btn-outline-secondary"
                    data-bs-toggle="collapse"
                    data-bs-target="#editGroup<?= $gid ?>">
                <i class="bi bi-pencil me-1"></i>Bewerken
            </button>
            <!-- Delete group form -->
            <form method="post"
                  action="<?= htmlspecialchars($basePath . '/admin/events/' . rawurlencode($slug) . '/opties/' . $gid . '/delete', ENT_QUOTES, 'UTF-8') ?>"
                  onsubmit="return confirm('Groep en alle items verwijderen?')">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Edit group form (collapsed by default) -->
    <div class="collapse" id="editGroup<?= $gid ?>">
        <div class="card-body border-bottom bg-light">
            <form method="post"
                  action="<?= htmlspecialchars($basePath . '/admin/events/' . rawurlencode($slug) . '/opties/' . $gid . '/update', ENT_QUOTES, 'UTF-8') ?>">
                <?= \App\Core\Csrf::field() ?>
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">Naam</label>
                        <input type="text" name="name" class="form-control"
                               value="<?= htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Max keuzes</label>
                        <input type="number" name="max_select" class="form-control" min="0"
                               value="<?= (int) $group['max_select'] ?>">
                        <div class="form-text">0 = uitgeschakeld</div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Volgorde</label>
                        <input type="number" name="sort_order" class="form-control"
                               value="<?= (int) $group['sort_order'] ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-center mt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_required"
                                   id="req<?= $gid ?>"
                                   <?= $group['is_required'] ? 'checked' : '' ?>>
                            <label class="form-check-label" for="req<?= $gid ?>">Verplicht</label>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-check-lg me-1"></i>Opslaan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Items list -->
    <div class="card-body">
        <?php if (empty($group['items'])): ?>
        <p class="text-muted mb-2">Nog geen items.</p>
        <?php else: ?>
        <table class="table table-sm table-bordered mb-3">
            <thead class="table-light">
                <tr>
                    <th>Naam</th>
                    <th>Min klas</th>
                    <th>Max klas</th>
                    <th>Volgorde</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($group['items'] as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= (int) $item['min_grade'] ?></td>
                    <td><?= (int) $item['max_grade'] ?></td>
                    <td><?= (int) $item['sort_order'] ?></td>
                    <td>
                        <form method="post"
                              action="<?= htmlspecialchars($basePath . '/admin/events/' . rawurlencode($slug) . '/opties/' . $gid . '/items/' . (int) $item['id'] . '/delete', ENT_QUOTES, 'UTF-8') ?>"
                              onsubmit="return confirm('Item verwijderen?')">
                            <?= \App\Core\Csrf::field() ?>
                            <button type="submit" class="btn btn-sm btn-outline-danger py-0">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <!-- Add item form -->
        <form method="post"
              action="<?= htmlspecialchars($basePath . '/admin/events/' . rawurlencode($slug) . '/opties/' . $gid . '/items', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Nieuw item</label>
                    <input type="text" name="name" class="form-control" placeholder="Naam" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Min klas (1-6)</label>
                    <input type="number" name="min_grade" class="form-control" min="1" max="6" value="1">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Max klas (1-6)</label>
                    <input type="number" name="max_grade" class="form-control" min="1" max="6" value="6">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Volgorde</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i>Toevoegen
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>

<!-- Add new group -->
<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>Nieuwe optiegroep toevoegen</h5>
    </div>
    <div class="card-body">
        <form method="post"
              action="<?= htmlspecialchars($basePath . '/admin/events/' . rawurlencode($slug) . '/opties', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Naam <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" placeholder="bijv. Film" required>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Max keuzes</label>
                    <input type="number" name="max_select" class="form-control" min="0" value="1">
                    <div class="form-text">0 = uitgeschakeld</div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Volgorde</label>
                    <input type="number" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-md-2 d-flex align-items-center mt-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_required" id="newGroupRequired">
                        <label class="form-check-label" for="newGroupRequired">Verplicht</label>
                    </div>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-plus-lg me-1"></i>Aanmaken
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
