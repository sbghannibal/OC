<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OC Acties</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
          integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
          crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?= htmlspecialchars(rtrim($basePath, '/'), ENT_QUOTES, 'UTF-8') ?>/css/app.css">
</head>
<body>
    <header class="oc-header">
        <div class="container">
            <a href="<?= htmlspecialchars(($basePath ?: '/'), ENT_QUOTES, 'UTF-8') ?>" class="oc-brand-link">
                <i class="bi bi-award-fill"></i>OC Acties
            </a>
        </div>
    </header>
    <main class="container pb-4">
        <?php include $viewFile; ?>
    </main>
    <footer class="oc-footer">
        <div class="container">© <?= date('Y') ?> OC Acties</div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
            integrity="sha384-YvpcrYf0tY3lHB60NNkmXc4s9bIOgUxi8T/jzmHxnOZrxPoaQdTqSMFKhUKF+sAm"
            crossorigin="anonymous"></script>
</body>
</html>