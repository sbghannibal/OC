<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>404 – Pagina niet gevonden</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
</head>
<body>
<div class="container py-5 text-center">
    <h1 class="display-4">404</h1>
    <p class="lead">Pagina niet gevonden.</p>
    <a href="<?= htmlspecialchars((\App\Core\View::basePath() ?: '/'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Terug naar home</a>
</div>
</body>
</html>
