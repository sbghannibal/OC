<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – OC Acties</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
</head>
<body>
<?php if (!empty($_SESSION['admin_ok'])): ?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?= htmlspecialchars($basePath . '/admin', ENT_QUOTES, 'UTF-8') ?>">OC Admin</a>
        <div class="navbar-nav ms-auto">
            <a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>">Evenementen</a>
            <form method="post" action="<?= htmlspecialchars($basePath . '/admin/logout', ENT_QUOTES, 'UTF-8') ?>" class="d-inline ms-2">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">Uitloggen</button>
            </form>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container pb-4">
    <?php include $viewFile; ?>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmHxnOZrxPoaQdTqSMFKhUKF+sAm"
        crossorigin="anonymous"></script>
</body>
</html>
