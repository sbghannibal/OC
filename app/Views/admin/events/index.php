<h1 class="mb-4">Evenementen</h1>

<a href="<?= htmlspecialchars($basePath . '/admin/events/new', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary mb-3">Nieuw evenement</a>

<?php if (empty($events)): ?>
<p class="text-muted">Nog geen evenementen aangemaakt.</p>
<?php else: ?>
<table class="table table-bordered table-striped">
    <thead class="table-dark">
        <tr>
            <th>Naam</th>
            <th>Slug</th>
            <th>Toegangscode</th>
            <th>Start</th>
            <th>Einde</th>
            <th>Status</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></td>
            <td><code><?= htmlspecialchars($event['slug'], ENT_QUOTES, 'UTF-8') ?></code></td>
            <td><code><?= htmlspecialchars($event['access_code'], ENT_QUOTES, 'UTF-8') ?></code></td>
            <td><?= htmlspecialchars($event['starts_at'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
            <td><?= htmlspecialchars($event['ends_at'] ?? '–', ENT_QUOTES, 'UTF-8') ?></td>
            <td>
                <?php if ($event['is_current']): ?>
                    <span class="badge bg-success">Huidig</span>
                <?php else: ?>
                    <span class="badge bg-secondary">Inactief</span>
                <?php endif; ?>
            </td>
            <td>
                <?php if (!$event['is_current']): ?>
                <form method="post" action="<?= htmlspecialchars($basePath . '/admin/events/current', ENT_QUOTES, 'UTF-8') ?>">
                    <?= \App\Core\Csrf::field() ?>
                    <input type="hidden" name="event_id" value="<?= (int) $event['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-success">Maak huidig</button>
                </form>
                <?php endif; ?>
                <a href="<?= htmlspecialchars($basePath . '/admin/events/' . rawurlencode($event['slug']) . '/opties', ENT_QUOTES, 'UTF-8') ?>"
                   class="btn btn-sm btn-outline-secondary mt-1">
                    <i class="bi bi-list-check me-1"></i>Opties
                </a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>
