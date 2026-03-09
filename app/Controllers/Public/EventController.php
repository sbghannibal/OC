<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\Child;
use App\Models\Event;
use App\Models\EventOptionGroup;
use App\Models\EventOptionItem;
use App\Models\OcClass;
use App\Models\Registration;

final class EventController
{
    public function __construct(private readonly array $config) {}

    /** GET /events – public list of all events */
    public function index(): void
    {
        $pdo    = Database::getInstance($this->config['db']);
        $events = Event::all($pdo);
        View::render('public/events/index', ['events' => $events]);
    }

    /** GET /events/{slug} – public detail page */
    public function show(string $slug): void
    {
        $pdo   = Database::getInstance($this->config['db']);
        $event = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        $success = false;
        if (!empty($_SESSION['registration_success_' . $slug])) {
            $success = true;
            unset($_SESSION['registration_success_' . $slug]);
        }

        View::render('public/events/show', ['event' => $event, 'success' => $success]);
    }

    /** GET /events/{slug}/deelnemen – registration form (requires parent login) */
    public function registerForm(string $slug): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (empty($_SESSION['parent_ok'])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';
            header('Location: ' . $basePath . '/ouder/login?return=' . rawurlencode($return));
            exit;
        }

        $parentId = (int) $_SESSION['parent_id'];
        $children = Child::findByParent($pdo, $parentId);
        $classes  = OcClass::all($pdo);
        $groups   = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
        foreach ($groups as &$group) {
            $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
        }
        unset($group);

        // Pre-fill form if ?kind=<child_id> is provided (edit flow)
        $prefillChildId  = (int) ($_GET['kind'] ?? 0);
        $existingReg     = null;
        $prefillItemIds  = [];
        $old             = [];

        if ($prefillChildId > 0) {
            $childRow = Child::findByIdAndParent($pdo, $prefillChildId, $parentId);
            if ($childRow !== null) {
                $existingReg = Registration::findByChildAndEvent($pdo, $prefillChildId, (int) $event['id']);
                if ($existingReg !== null && $existingReg['cancelled_at'] === null) {
                    $prefillItemIds = EventOptionItem::findIdsByRegistration($pdo, (int) $existingReg['id']);
                    $old = [
                        'telefoon'     => $existingReg['telefoon'] ?? '',
                        'opmerking'    => $existingReg['opmerking'] ?? '',
                        'child_select' => 'existing_' . $prefillChildId,
                        'items'        => $prefillItemIds,
                    ];
                } else {
                    $old = ['child_select' => 'existing_' . $prefillChildId];
                }
            }
        }

        View::render('public/events/register', [
            'event'            => $event,
            'classes'          => $classes,
            'groups'           => $groups,
            'children'         => $children,
            'errors'           => [],
            'old'              => $old,
            'duplicateChild'   => null,
            'pendingChildData' => [],
        ]);
    }

    /** POST /events/{slug}/deelnemen – process registration */
    public function registerSubmit(string $slug): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (empty($_SESSION['parent_ok'])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';
            header('Location: ' . $basePath . '/ouder/login?return=' . rawurlencode($return));
            exit;
        }

        if (!Csrf::verify()) {
            $parentId = (int) $_SESSION['parent_id'];
            $children = Child::findByParent($pdo, $parentId);
            $classes  = OcClass::all($pdo);
            $groups   = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
            foreach ($groups as &$group) {
                $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
            }
            unset($group);
            View::render('public/events/register', [
                'event'           => $event,
                'classes'         => $classes,
                'groups'          => $groups,
                'children'        => $children,
                'errors'          => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'             => [],
                'duplicateChild'  => null,
                'pendingChildData' => [],
            ]);
            return;
        }

        $parentId   = (int) $_SESSION['parent_id'];
        $parentEmail = (string) ($_SESSION['parent_email'] ?? '');

        // ── Determine child ──────────────────────────────────────────────────
        $childSelect  = trim((string) ($_POST['child_select']  ?? ''));
        $childAction  = trim((string) ($_POST['child_action']  ?? ''));
        $confirmChildId = (int) ($_POST['confirm_child_id'] ?? 0);
        $telefoon     = trim((string) ($_POST['telefoon']   ?? ''));
        $opmerking    = trim((string) ($_POST['opmerking']  ?? ''));

        $errors       = [];
        $childRow     = null;  // resolved child array
        $klasId       = 0;
        $klasRow      = null;
        $duplicateChild    = null;
        $pendingChildData  = [];

        if (str_starts_with($childSelect, 'existing_')) {
            // ── Existing child selected ──────────────────────────────────────
            $selectedChildId = (int) substr($childSelect, strlen('existing_'));
            $childRow = Child::findByIdAndParent($pdo, $selectedChildId, $parentId);
            if ($childRow === null) {
                $errors[] = 'Ongeldig kind geselecteerd.';
            } else {
                $klasId  = (int) ($childRow['klas_id'] ?? 0);
                $klasRow = $klasId > 0 ? OcClass::findById($pdo, $klasId) : null;
            }
        } elseif ($childSelect === 'new') {
            // ── New child ────────────────────────────────────────────────────
            $childFirstName = trim((string) ($_POST['child_first_name'] ?? ''));
            $childLastName  = trim((string) ($_POST['child_last_name']  ?? ''));
            $childBirthdate = trim((string) ($_POST['child_birthdate']  ?? ''));
            $klasId         = (int) ($_POST['klas_id'] ?? 0);

            if ($childFirstName === '') {
                $errors[] = 'Voornaam van het kind is verplicht.';
            }
            if ($klasId <= 0) {
                $errors[] = 'Klas is verplicht.';
            } else {
                $klasRow = OcClass::findById($pdo, $klasId);
                if ($klasRow === null) {
                    $errors[] = 'Ongeldige klas geselecteerd.';
                    $klasId   = 0;
                }
            }

            if ($errors === []) {
                // Duplicate detection
                if ($childAction === 'use_existing' && $confirmChildId > 0) {
                    // Parent chose to reuse an existing child
                    $childRow = Child::findByIdAndParent($pdo, $confirmChildId, $parentId);
                    if ($childRow === null) {
                        $errors[] = 'Ongeldig kind geselecteerd.';
                    } else {
                        $klasId  = (int) ($childRow['klas_id'] ?? $klasId);
                        $klasRow = $klasId > 0 ? OcClass::findById($pdo, $klasId) : $klasRow;
                    }
                } else {
                    // Check for duplicate before creating
                    $duplicate = ($childAction !== 'create_new')
                        ? Child::findLikelyDuplicate(
                            $pdo, $parentId,
                            $childFirstName,
                            $childLastName !== '' ? $childLastName : null,
                            $childBirthdate !== '' ? $childBirthdate : null,
                            $klasId > 0 ? $klasId : null
                        )
                        : null;

                    if ($duplicate !== null && $childAction !== 'create_new') {
                        // Show duplicate-confirm UI (re-render form)
                        $children = Child::findByParent($pdo, $parentId);
                        $classes  = OcClass::all($pdo);
                        $groups   = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
                        foreach ($groups as &$group) {
                            $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
                        }
                        unset($group);
                        View::render('public/events/register', [
                            'event'            => $event,
                            'classes'          => $classes,
                            'groups'           => $groups,
                            'children'         => $children,
                            'errors'           => [],
                            'old'              => [
                                'telefoon'  => $telefoon,
                                'opmerking' => $opmerking,
                                'klas_id'   => (string) $klasId,
                            ],
                            'duplicateChild'   => $duplicate,
                            'pendingChildData' => [
                                'first_name' => $childFirstName,
                                'last_name'  => $childLastName,
                                'birthdate'  => $childBirthdate,
                                'klas_id'    => $klasId,
                            ],
                        ]);
                        return;
                    }

                    // Create new child
                    $newChildId = Child::create($pdo, [
                        'parent_id'  => $parentId,
                        'first_name' => $childFirstName,
                        'last_name'  => $childLastName !== '' ? $childLastName : null,
                        'birthdate'  => $childBirthdate !== '' ? $childBirthdate : null,
                        'klas_id'    => $klasId > 0 ? $klasId : null,
                        'klas_name'  => $klasRow !== null ? (string) $klasRow['name'] : null,
                    ]);
                    $childRow = Child::findByIdAndParent($pdo, $newChildId, $parentId);
                }
            }
        } else {
            $errors[] = 'Kies een kind om in te schrijven.';
        }

        // ── Validate contact fields ──────────────────────────────────────────
        if ($telefoon === '') {
            $errors[] = 'Telefoonnummer is verplicht.';
        } elseif (!Registration::validateBelgianMobile($telefoon)) {
            $errors[] = 'Vul een geldig Belgisch gsm-nummer in (bijv. 0470 12 34 56 of +32 470 12 34 56).';
        }

        // ── Validate option selections ───────────────────────────────────────
        $classRank = ($klasRow !== null) ? (int) ($klasRow['rank'] ?? 0) : 0;
        $grade     = ($klasRow !== null) ? (OcClass::gradeFromName((string) $klasRow['name']) ?? 0) : 0;
        $groups = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
        foreach ($groups as &$group) {
            $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
        }
        unset($group);

        $chosenItemIds = [];
        foreach ($groups as $group) {
            $groupId   = (int) $group['id'];
            $maxSelect = (int) $group['max_select'];
            $required  = (bool) $group['is_required'];

            $raw = $_POST['option_group_' . $groupId] ?? [];
            if (!is_array($raw)) {
                $raw = [$raw];
            }
            $raw = array_map('intval', $raw);

            if ($classRank > 0) {
                $allowedItems = EventOptionItem::findByGroupForClassRank($pdo, $groupId, $classRank);
            } elseif ($grade > 0) {
                $allowedItems = EventOptionItem::findByGroupForGrade($pdo, $groupId, $grade);
            } else {
                $allowedItems = $group['items'];
            }
            $allowedIds = array_map(static fn($i) => (int) $i['id'], $allowedItems);

            $valid = array_values(array_intersect($raw, $allowedIds));

            if ($maxSelect === 0) {
                $valid = [];
            } elseif (count($valid) > $maxSelect) {
                $groupLabel = htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
                $errors[]   = sprintf('Je mag maximaal %d keuze(s) maken voor "%s".', $maxSelect, $groupLabel);
                $valid      = array_slice($valid, 0, $maxSelect);
            }

            if ($required && $maxSelect > 0 && count($valid) === 0 && !empty($allowedIds)) {
                $groupLabel = htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
                $errors[]   = sprintf('"%s" is verplicht.', $groupLabel);
            }

            $chosenItemIds = array_merge($chosenItemIds, $valid);
        }

        if ($errors !== []) {
            $children = Child::findByParent($pdo, $parentId);
            $classes  = OcClass::all($pdo);
            View::render('public/events/register', [
                'event'            => $event,
                'classes'          => $classes,
                'groups'           => $groups,
                'children'         => $children,
                'errors'           => $errors,
                'old'              => [
                    'telefoon'     => $telefoon,
                    'opmerking'    => $opmerking,
                    'klas_id'      => (string) $klasId,
                    'child_select' => $childSelect,
                    'items'        => $chosenItemIds,
                ],
                'duplicateChild'   => null,
                'pendingChildData' => [],
            ]);
            return;
        }

        // ── Build naam from child ────────────────────────────────────────────
        $naam = '';
        if ($childRow !== null) {
            $naam = (string) $childRow['first_name'];
            if (!empty($childRow['last_name'])) {
                $naam .= ' ' . (string) $childRow['last_name'];
            }
        }

        // ── Upsert: update existing registration or create new ───────────────
        $existingReg = ($childRow !== null)
            ? Registration::findByChildAndEvent($pdo, (int) $childRow['id'], (int) $event['id'])
            : null;

        if ($existingReg !== null) {
            $regId = (int) $existingReg['id'];
            Registration::updateRegistration($pdo, $regId, [
                'naam'      => $naam,
                'email'     => $parentEmail,
                'telefoon'  => $telefoon,
                'opmerking' => $opmerking !== '' ? $opmerking : null,
            ]);
        } else {
            $regId = Registration::create($pdo, [
                'event_id'  => (int) $event['id'],
                'naam'      => $naam,
                'email'     => $parentEmail,
                'telefoon'  => $telefoon,
                'klas_id'   => $klasId > 0 ? $klasId : null,
                'klas_name' => $klasRow !== null ? (string) $klasRow['name'] : null,
                'opmerking' => $opmerking !== '' ? $opmerking : null,
                'parent_id' => $parentId,
                'child_id'  => $childRow !== null ? (int) $childRow['id'] : null,
            ]);
        }

        EventOptionItem::setForRegistration($pdo, $regId, $chosenItemIds);

        if (!empty($_SESSION['parent_ok'])) {
            header('Location: ' . $basePath . '/events/' . rawurlencode($slug) . '/overzicht');
        } else {
            $_SESSION['registration_success_' . $slug] = true;
            header('Location: ' . $basePath . '/events/' . rawurlencode($slug));
        }
        exit;
    }

    /**
     * GET /events/{slug}/overzicht – parent overview of their registrations for the event
     */
    public function overview(string $slug): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (empty($_SESSION['parent_ok'])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/overzicht';
            header('Location: ' . $basePath . '/ouder/login?return=' . rawurlencode($return));
            exit;
        }

        $parentId     = (int) $_SESSION['parent_id'];
        $registrations = Registration::findByParentAndEvent($pdo, $parentId, (int) $event['id']);
        $isOpen       = Event::isRegistrationOpen($event);

        // Load child info and chosen options per registration
        $regRows = [];
        $eventTotal = 0.0;
        foreach ($registrations as $reg) {
            $childId  = (int) ($reg['child_id'] ?? 0);
            $child    = $childId > 0 ? Child::findByIdAndParent($pdo, $childId, $parentId) : null;
            $options  = EventOptionItem::findChosenForRegistration($pdo, (int) $reg['id']);
            $subtotal = (float) array_sum(array_map('floatval', array_column($options, 'price')));
            $eventTotal += $subtotal;
            $regRows[] = [
                'registration' => $reg,
                'child'        => $child,
                'options'      => $options,
                'subtotal'     => $subtotal,
            ];
        }

        View::render('public/events/overview', [
            'event'      => $event,
            'regRows'    => $regRows,
            'eventTotal' => $eventTotal,
            'isOpen'     => $isOpen,
        ]);
    }

    /**
     * POST /events/{slug}/afmelden – soft-cancel a child's registration
     */
    public function afmelden(string $slug): void
    {
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (empty($_SESSION['parent_ok'])) {
            header('Location: ' . $basePath . '/ouder/login');
            exit;
        }

        if (!Csrf::verify()) {
            header('Location: ' . $basePath . '/events/' . rawurlencode($slug) . '/overzicht');
            exit;
        }

        if (!Event::isRegistrationOpen($event)) {
            header('Location: ' . $basePath . '/events/' . rawurlencode($slug) . '/overzicht');
            exit;
        }

        $parentId = (int) $_SESSION['parent_id'];
        $regId    = (int) ($_POST['registration_id'] ?? 0);

        if ($regId > 0) {
            $reg = Registration::findByIdAndParent($pdo, $regId, $parentId);
            if ($reg !== null) {
                Registration::cancel($pdo, $regId);
            }
        }

        header('Location: ' . $basePath . '/events/' . rawurlencode($slug) . '/overzicht');
        exit;
    }

    /**
     * GET /events/{slug}/qr?ts=...&sig=...
     *
     * One-step QR bypass: validates HMAC signature and expiry, then grants
     * access to the registration form without entering an access code.
     */
    public function qrBypass(string $slug): void
    {
        $basePath   = $this->config['base_path'] ?? '';
        $signingKey = $this->config['signing_key'] ?? '';

        $ts  = trim((string) ($_GET['ts']  ?? ''));
        $sig = trim((string) ($_GET['sig'] ?? ''));

        $deelnemenUrl = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';

        // All validation failures use the same 403 response to avoid leaking
        // information about which step failed (signing key presence, expiry, signature).
        $deny = static function (): void {
            http_response_code(403);
            View::render('errors/404', []);
        };

        if ($ts === '' || $sig === '' || $signingKey === '') {
            $deny();
            return;
        }

        // Enforce 7-day expiry window
        $timestamp = (int) $ts;
        if ($timestamp <= 0 || abs(time() - $timestamp) > 7 * 86400) {
            $deny();
            return;
        }

        // Verify HMAC signature (always compute expected to avoid timing issues)
        $expected = hash_hmac('sha256', $slug . '.' . $ts, $signingKey);
        if (!hash_equals($expected, $sig)) {
            $deny();
            return;
        }

        // Verify the event exists
        $pdo   = Database::getInstance($this->config['db']);
        $event = Event::findBySlug($pdo, $slug);
        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        // Grant access and redirect to registration form
        $_SESSION['access_ok_' . $slug] = true;
        header('Location: ' . $deelnemenUrl);
        exit;
    }
}
