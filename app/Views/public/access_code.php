<?php if ($error !== null): ?>
<div class="alert alert-danger" role="alert">
    <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
</div>
<?php endif; ?>

<div class="row justify-content-center">
    <div class="col-12 col-sm-8 col-md-5">
        <h1 class="h4 mb-3">Toegangscode invoeren</h1>
        <form method="post" action="/toegang">
            <div class="mb-3">
                <label for="code" class="form-label">Toegangscode</label>
                <input type="text" class="form-control" id="code" name="code"
                       autocomplete="off" autofocus required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Toegang verkrijgen</button>
        </form>
    </div>
</div>