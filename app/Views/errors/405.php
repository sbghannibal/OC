<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>405 – Methode niet toegestaan</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars((\App\Core\View::basePath() ?: ''), ENT_QUOTES, 'UTF-8') ?>/css/app.css">
</head>
<body>
<div class="container py-5 text-center">
    <i class="bi bi-slash-circle text-secondary" style="font-size:4rem"></i>
    <h1 class="display-4 mt-3">405</h1>
    <p class="lead text-muted">Methode niet toegestaan.</p>
    <a href="<?= htmlspecialchars((\App\Core\View::basePath() ?: '/'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">
        <i class="bi bi-house me-1"></i>Terug naar home
    </a>
</div>
</body>
</html>
