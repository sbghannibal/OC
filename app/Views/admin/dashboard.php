<div class="d-flex align-items-center mb-4">
    <i class="bi bi-speedometer2 fs-3 me-3 text-primary"></i>
    <h1 class="mb-0">Admin Dashboard</h1>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card oc-card-events h-100">
            <div class="card-body">
                <div class="d-flex align-items-start mb-2">
                    <i class="bi bi-calendar-event fs-4 text-primary me-2 mt-1"></i>
                    <div>
                        <h5 class="card-title mb-1">Evenementen</h5>
                        <p class="card-text text-muted small">Bekijk en beheer evenementen.</p>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($basePath . '/admin/events', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-primary">Bekijk evenementen</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card oc-card-new h-100">
            <div class="card-body">
                <div class="d-flex align-items-start mb-2">
                    <i class="bi bi-plus-circle fs-4 text-success me-2 mt-1"></i>
                    <div>
                        <h5 class="card-title mb-1">Nieuw evenement</h5>
                        <p class="card-text text-muted small">Maak een nieuw evenement aan met toegangscode.</p>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($basePath . '/admin/events/new', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary">Evenement aanmaken</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card oc-card-users h-100">
            <div class="card-body">
                <div class="d-flex align-items-start mb-2">
                    <i class="bi bi-people fs-4 me-2 mt-1" style="color:#9333ea"></i>
                    <div>
                        <h5 class="card-title mb-1">Gebruikers</h5>
                        <p class="card-text text-muted small">Beheer admin-accounts die toegang hebben tot dit dashboard.</p>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($basePath . '/admin/users', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Gebruikers beheren</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card oc-card-audit h-100">
            <div class="card-body">
                <div class="d-flex align-items-start mb-2">
                    <i class="bi bi-journal-text fs-4 me-2 mt-1" style="color:#d97706"></i>
                    <div>
                        <h5 class="card-title mb-1">Activiteitenlog</h5>
                        <p class="card-text text-muted small">Bekijk wie wat heeft gedaan in het systeem.</p>
                    </div>
                </div>
                <a href="<?= htmlspecialchars($basePath . '/admin/audit-log', ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-secondary">Bekijk log</a>
            </div>
        </div>
    </div>
</div>
