<?php

declare(strict_types=1);

namespace App\Controllers\Public;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
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

    /** GET /events/{slug}/deelnemen – registration form (requires access code) */
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

        if (empty($_SESSION['access_ok_' . $slug])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';
            header('Location: ' . $basePath . '/toegang?return=' . rawurlencode($return));
            exit;
        }

        $classes = OcClass::all($pdo);
        $groups  = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
        // Items per group (all grades, filtered client-side via JS and server-side on submit)
        foreach ($groups as &$group) {
            $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
        }
        unset($group);

        View::render('public/events/register', [
            'event'   => $event,
            'classes' => $classes,
            'groups'  => $groups,
            'errors'  => [],
            'old'     => [],
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

        if (empty($_SESSION['access_ok_' . $slug])) {
            $return = $basePath . '/events/' . rawurlencode($slug) . '/deelnemen';
            header('Location: ' . $basePath . '/toegang?return=' . rawurlencode($return));
            exit;
        }

        if (!Csrf::verify()) {
            $classes = OcClass::all($pdo);
            $groups  = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
            foreach ($groups as &$group) {
                $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
            }
            unset($group);
            View::render('public/events/register', [
                'event'   => $event,
                'classes' => $classes,
                'groups'  => $groups,
                'errors'  => ['Ongeldig formulierverzoek. Probeer opnieuw.'],
                'old'     => [],
            ]);
            return;
        }

        $naam      = trim((string) ($_POST['naam']      ?? ''));
        $email     = trim((string) ($_POST['email']     ?? ''));
        $telefoon  = trim((string) ($_POST['telefoon']  ?? ''));
        $klasId    = (int) ($_POST['klas_id'] ?? 0);
        $opmerking = trim((string) ($_POST['opmerking'] ?? ''));

        $errors = [];
        if ($naam === '') {
            $errors[] = 'Naam is verplicht.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Vul een geldig e-mailadres in.';
        }
        if ($telefoon === '') {
            $errors[] = 'Telefoonnummer is verplicht.';
        } elseif (!Registration::validateBelgianMobile($telefoon)) {
            $errors[] = 'Vul een geldig Belgisch gsm-nummer in (bijv. 0470 12 34 56 of +32 470 12 34 56).';
        }

        // Validate class
        $klasRow = null;
        if ($klasId <= 0) {
            $errors[] = 'Klas is verplicht.';
        } else {
            $klasRow = OcClass::findById($pdo, $klasId);
            if ($klasRow === null) {
                $errors[] = 'Ongeldige klas geselecteerd.';
                $klasId   = 0;
            }
        }

        // Determine grade for option visibility
        $grade = ($klasRow !== null) ? (OcClass::gradeFromName((string) $klasRow['name']) ?? 0) : 0;

        // Validate option selections
        $groups        = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
        $selectedItems = []; // group_id -> list<int>
        foreach ($groups as &$group) {
            $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
        }
        unset($group);

        $chosenItemIds = []; // flat list of valid item IDs to save
        foreach ($groups as $group) {
            $groupId   = (int) $group['id'];
            $maxSelect = (int) $group['max_select'];
            $required  = (bool) $group['is_required'];

            // IDs submitted for this group
            $raw = $_POST['option_group_' . $groupId] ?? [];
            if (!is_array($raw)) {
                $raw = [$raw];
            }
            $raw = array_map('intval', $raw);

            // Determine allowed item IDs for this grade
            $allowedItems = ($grade > 0)
                ? EventOptionItem::findByGroupForGrade($pdo, $groupId, $grade)
                : $group['items'];
            $allowedIds = array_map(static fn($i) => (int) $i['id'], $allowedItems);

            // Filter submitted IDs to allowed only
            $valid = array_values(array_intersect($raw, $allowedIds));

            // Enforce max (0 means disabled / not selectable)
            if ($maxSelect === 0) {
                $valid = [];
            } elseif (count($valid) > $maxSelect) {
                $groupLabel = htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
                $errors[]   = sprintf('Je mag maximaal %d keuze(s) maken voor "%s".', $maxSelect, $groupLabel);
                $valid      = array_slice($valid, 0, $maxSelect);
            }

            // Required check
            if ($required && $maxSelect > 0 && count($valid) === 0 && !empty($allowedIds)) {
                $groupLabel = htmlspecialchars($group['name'], ENT_QUOTES, 'UTF-8');
                $errors[]   = sprintf('"%s" is verplicht.', $groupLabel);
            }

            $selectedItems[$groupId] = $valid;
            $chosenItemIds           = array_merge($chosenItemIds, $valid);
        }

        if ($errors !== []) {
            View::render('public/events/register', [
                'event'    => $event,
                'classes'  => OcClass::all($pdo),
                'groups'   => $groups,
                'errors'   => $errors,
                'old'      => [
                    'naam'      => $naam,
                    'email'     => $email,
                    'telefoon'  => $telefoon,
                    'klas_id'   => (string) $klasId,
                    'opmerking' => $opmerking,
                    'items'     => $chosenItemIds,
                ],
            ]);
            return;
        }

        $regId = Registration::create($pdo, [
            'event_id'  => (int) $event['id'],
            'naam'      => $naam,
            'email'     => $email,
            'telefoon'  => $telefoon,
            'klas_id'   => $klasId > 0 ? $klasId : null,
            'klas_name' => $klasRow !== null ? (string) $klasRow['name'] : null,
            'opmerking' => $opmerking !== '' ? $opmerking : null,
        ]);

        // Save chosen option items
        EventOptionItem::setForRegistration($pdo, $regId, $chosenItemIds);

        $_SESSION['registration_success_' . $slug] = true;
        header('Location: ' . $basePath . '/events/' . rawurlencode($slug));
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
