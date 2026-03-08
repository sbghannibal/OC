<h1 class="mb-4">Nieuw evenement aanmaken</h1>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $err): ?>
        <li><?= htmlspecialchars($err, ENT_QUOTES, 'UTF-8') ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-12 col-md-7">
        <form method="post" action="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label for="name" class="form-label">Naam <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="name" name="name"
                       value="<?= htmlspecialchars($old['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required autofocus>
            </div>
            <div class="mb-3">
                <label for="slug" class="form-label">Slug <small class="text-muted">(automatisch gegenereerd indien leeg)</small></label>
                <input type="text" class="form-control" id="slug" name="slug"
                       value="<?= htmlspecialchars($old['slug'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       pattern="[a-z0-9\-]+" placeholder="bijv. lourdes-2025">
            </div>
            <div class="mb-3">
                <label for="access_code" class="form-label">Toegangscode <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="access_code" name="access_code"
                       value="<?= htmlspecialchars($old['accessCode'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
            </div>
            <div class="mb-3">
                <label for="starts_at" class="form-label">Start datum/tijd <small class="text-muted">(optioneel)</small></label>
                <input type="datetime-local" class="form-control" id="starts_at" name="starts_at"
                       value="<?= htmlspecialchars($old['startsAt'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="mb-3">
                <label for="ends_at" class="form-label">Eind datum/tijd <small class="text-muted">(optioneel)</small></label>
                <input type="datetime-local" class="form-control" id="ends_at" name="ends_at"
                       value="<?= htmlspecialchars($old['endsAt'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Aanmaken</button>
                <a href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Annuleren</a>
            </div>
        </form>
    </div>
</div>
