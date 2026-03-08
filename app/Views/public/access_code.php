<?php if ($rateLimited): ?>
<div class="alert alert-warning" role="alert">
    <i class="bi bi-exclamation-triangle-fill me-2"></i>Te veel pogingen. Wacht een paar minuten en probeer opnieuw.
</div>
<?php elseif ($error !== null): ?>
<div class="alert alert-danger" role="alert">
    <i class="bi bi-x-circle-fill me-2"></i><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="oc-auth-wrap">
    <div class="card oc-auth-card">
        <div class="oc-card-header">
            <h1><i class="bi bi-key-fill me-2"></i>Toegangscode invoeren</h1>
        </div>
        <?php if (!$rateLimited): ?>
        <div class="card-body">
            <form method="post" action="<?= htmlspecialchars($basePath . '/toegang' . ($next !== '' ? '?next=' . urlencode($next) : ''), ENT_QUOTES, 'UTF-8') ?>">
                <?= \App\Core\Csrf::field() ?>
                <div class="mb-3">
                    <label for="code" class="form-label">Toegangscode</label>
                    <input type="text" class="form-control form-control-lg" id="code" name="code"
                           autocomplete="off" autofocus required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-unlock-fill me-2"></i>Toegang verkrijgen
                </button>
            </form>
        </div>
        <?php else: ?>
        <div class="card-body text-center text-muted py-4">
            <i class="bi bi-clock-history fs-2 mb-2 d-block"></i>
            Probeer het later opnieuw.
        </div>
        <?php endif; ?>
    </div>
</div>