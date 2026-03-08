<h1 class="mb-4">Gebruikers</h1>

<a href="<?= htmlspecialchars($basePath . '/admin/users/new', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary mb-4">Nieuwe gebruiker</a>

<?php if (empty($users)): ?>
    <div class="alert alert-info">Er zijn nog geen gebruikers aangemaakt.</div>
<?php else: ?>
<div class="table-responsive">
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>#</th>
                <th>Gebruikersnaam</th>
                <th>Aangemaakt op</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= (int) $user['id'] ?></td>
                <td><?= htmlspecialchars((string) $user['username'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string) $user['created_at'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if ((int) $user['id'] !== (int) ($_SESSION['admin_user_id'] ?? 0)): ?>
                    <form method="post" action="<?= htmlspecialchars($basePath . '/admin/users/delete', ENT_QUOTES, 'UTF-8') ?>"
                          onsubmit="return confirm('Gebruiker verwijderen?')">
                        <?= \App\Core\Csrf::field() ?>
                        <input type="hidden" name="user_id" value="<?= (int) $user['id'] ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Verwijderen</button>
                    </form>
                    <?php else: ?>
                    <span class="badge bg-secondary">Jij</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>
