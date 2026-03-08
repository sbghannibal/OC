<h1 class="mb-4">Activiteitenlog</h1>

<?php if (empty($entries)): ?>
    <div class="alert alert-info">Er zijn nog geen activiteiten geregistreerd.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-sm table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Tijdstip</th>
                <th>Gebruiker</th>
                <th>Actie</th>
                <th>Details</th>
                <th>IP-adres</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($entries as $entry): ?>
            <tr>
                <td class="text-nowrap"><?= htmlspecialchars((string) $entry['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $entry['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><code><?= htmlspecialchars((string) $entry['action'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= htmlspecialchars((string) ($entry['details'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td class="text-nowrap"><?= htmlspecialchars((string) ($entry['ip_address'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<p class="text-muted"><small>Toont de laatste <?= count($entries) ?> activiteiten.</small></p>
<?php endif; ?>
