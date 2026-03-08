<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin – OC Acties</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(rtrim($basePath, '/'), ENT_QUOTES, 'UTF-8') ?>/css/app.css">
</head>
<body>
<?php if (!empty($_SESSION['admin_ok'])): ?>
<nav class="navbar navbar-expand-lg oc-admin-nav mb-4">
    <div class="container">
        <a class="navbar-brand text-white" href="<?= htmlspecialchars($basePath . '/admin', ENT_QUOTES, 'UTF-8') ?>">
            <i class="bi bi-shield-lock-fill me-1"></i>OC Admin
        </a>
        <button class="navbar-toggler border-light" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-calendar-event"></i>Evenementen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/inschrijvingen', ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-people-fill"></i>Inschrijvingen
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/users', ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-people"></i>Gebruikers
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?= htmlspecialchars($basePath . '/admin/audit-log', ENT_QUOTES, 'UTF-8') ?>">
                        <i class="bi bi-journal-text"></i>Activiteitenlog
                    </a>
                </li>
            </ul>
            <span class="navbar-text me-3" style="color:rgba(255,255,255,.6)">
                <small><i class="bi bi-person-circle me-1"></i><?= htmlspecialchars((string) ($_SESSION['admin_username'] ?? ''), ENT_QUOTES, 'UTF-8') ?></small>
            </span>
            <form method="post" action="<?= htmlspecialchars($basePath . '/admin/logout', ENT_QUOTES, 'UTF-8') ?>" class="d-inline">
                <?= \App\Core\Csrf::field() ?>
                <button type="submit" class="btn btn-sm btn-outline-light">
                    <i class="bi bi-box-arrow-right me-1"></i>Uitloggen
                </button>
            </form>
        </div>
    </div>
</nav>
<?php endif; ?>
<div class="container pb-4">
    <?php include $viewFile; ?>
</div>
<footer class="oc-footer">
    <div class="container">© <?= date('Y') ?> OC Acties</div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmHxnOZrxPoaQdTqSMFKhUKF+sAm"
        crossorigin="anonymous"></script>
</body>
</html>
