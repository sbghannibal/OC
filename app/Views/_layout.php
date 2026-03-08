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
    <nav class="navbar oc-public-nav navbar-dark navbar-expand-lg mb-4">
        <div class="container">
            <a class="navbar-brand oc-brand-link" href="<?= htmlspecialchars(($basePath ?: '/'), ENT_QUOTES, 'UTF-8') ?>">
                <i class="bi bi-award-fill me-1"></i>OC Acties
            </a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#publicNavbar" aria-controls="publicNavbar"
                    aria-expanded="false" aria-label="Menu openen">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="publicNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= htmlspecialchars($basePath . '/events', ENT_QUOTES, 'UTF-8') ?>">
                            <i class="bi bi-calendar-event me-1"></i>Evenementen
                        </a>
                    </li>
                </ul>
                <div class="mt-2 mt-lg-0">
                    <a href="<?= htmlspecialchars($basePath . '/toegang', ENT_QUOTES, 'UTF-8') ?>"
                       class="btn btn-light btn-sm fw-semibold px-3">
                        <i class="bi bi-box-arrow-in-right me-1"></i>Toegangscode
                    </a>
                </div>
            </div>
        </div>
    </nav>
    <main class="container pb-5">
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