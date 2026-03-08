<h1 class="mb-4">Admin Dashboard</h1>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Evenementen</h5>
                <p class="card-text">Bekijk en beheer evenementen.</p>
                <a href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary">Bekijk evenementen</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Nieuw evenement</h5>
                <p class="card-text">Maak een nieuw evenement aan met toegangscode.</p>
                <a href="<?= htmlspecialchars($basePath . '/admin/events/new', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Evenement aanmaken</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Gebruikers</h5>
                <p class="card-text">Beheer admin-accounts die toegang hebben tot dit dashboard.</p>
                <a href="<?= htmlspecialchars($basePath . '/admin/users', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Gebruikers beheren</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Activiteitenlog</h5>
                <p class="card-text">Bekijk wie wat heeft gedaan in het systeem.</p>
                <a href="<?= htmlspecialchars($basePath . '/admin/audit-log', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Bekijk log</a>
            </div>
        </div>
    </div>
</div>
