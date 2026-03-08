<?php if ($rateLimited): ?>
<div class="alert alert-warning" role="alert">
    Te veel pogingen. Wacht een paar minuten en probeer opnieuw.
</div>
<?php elseif ($error !== null): ?>
<div class="alert alert-danger" role="alert">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-sm-8 col-md-5">
        <h1 class="h4 mb-3">Toegangscode invoeren</h1>
        <?php if (!$rateLimited): ?>
        <form method="post" action="<?= htmlspecialchars($basePath . '/toegang', ENT_QUOTES, 'UTF-8') ?>">
            <?= \App\Core\Csrf::field() ?>
            <div class="mb-3">
                <label for="code" class="form-label">Toegangscode</label>
                <input type="text" class="form-control" id="code" name="code"
                       autocomplete="off" autofocus required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Toegang verkrijgen</button>
        </form>
        <?php endif; ?>
    </div>
</div>