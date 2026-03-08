<?php if ($event !== null): ?>
<h1 class="mb-3">Welkom bij <?= htmlspecialchars($event['name'], ENT_QUOTES, 'UTF-8') ?></h1>
<p>U bent succesvol ingelogd. Er zijn momenteel geen acties beschikbaar.</p>
<?php else: ?>
<h1 class="mb-3">Welkom</h1>
<p>Er is nog geen evenement aangemaakt. <a href="<?= htmlspecialchars($basePath . '/admin', ENT_QUOTES, 'UTF-8') ?>">Ga naar het admin dashboard</a> om een evenement aan te maken.</p>
<?php endif; ?>