<?php
/** @var list<array<string,mixed>> $classes */
/** @var string|null $error */
/** @var string|null $success */
?>
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <h1 class="mb-0">Klassen</h1>
    <a href="<?= htmlspecialchars($basePath . '/admin/klassen/new', ENT_QUOTES, 'UTF-8') ?>"
       class="btn btn-primary">
        <i class="bi bi-plus-lg me-1"></i>Nieuwe klas
    </a>
</div>

<?php if ($error !== null): ?>
<div class="alert alert-danger">
    <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<?php if ($success !== null): ?>
<div class="alert alert-success">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<?php if (empty($classes)): ?>
<p class="text-muted">Nog geen klassen aangemaakt.</p>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Naam</th>
                <th>Aangemaakt op</th>
                <th>Actie</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($classes as $cls): ?>
            <tr>
                <td class="text-muted"><?= (int) $cls['id'] ?></td>
                <td><strong><?= htmlspecialchars($cls['name'], ENT_QUOTES, 'UTF-8') ?></strong></td>
                <td><?= htmlspecialchars($cls['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <form method="post"
                          action="<?= htmlspecialchars($basePath . '/admin/klassen/delete', ENT_QUOTES, 'UTF-8') ?>"
                          onsubmit="return confirm('Klas <?= htmlspecialchars($cls['name'], ENT_QUOTES, 'UTF-8') ?> verwijderen?')">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="id" value="<?= (int) $cls['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">
                            <i class="bi bi-trash me-1"></i>Verwijderen
                        </button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
