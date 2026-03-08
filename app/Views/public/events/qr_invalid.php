<?php
/** @var array<string,mixed> $event */
/** @var string $reason */
?>
<div class="row justify-content-center">
    <div class="col-sm-10 col-md-7 col-lg-5">
        <div class="alert alert-danger text-center p-4" role="alert">
            <i class="bi bi-x-circle-fill fs-1 d-block mb-3"></i>
            <h4 class="alert-heading">Ongeldige QR-link</h4>
            <p class="mb-0">
                <?php if ($reason === 'verlopen'): ?>
                Deze registratielink is verlopen. Vraag een nieuwe QR-code aan.
                <?php else: ?>
                Deze registratielink is ongeldig. Controleer de link en probeer opnieuw.
                <?php endif; ?>
            </p>
        </div>
    </div>
</div>
