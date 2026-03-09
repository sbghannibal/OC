<?php

declare(strict_types=1);

namespace App\Controllers\Admin;

use App\Core\Csrf;
use App\Core\Database;
use App\Core\View;
use App\Models\Event;
use App\Models\EventOptionGroup;
use App\Models\EventOptionItem;
use App\Models\OcClass;

final class EventOptionsController
{
    public function __construct(private readonly array $config) {}

    private function requireAuth(): void
    {
        if (empty($_SESSION['admin_ok'])) {
            $basePath = $this->config['base_path'] ?? '';
            header('Location: ' . $basePath . '/admin/login');
            exit;
        }
    }

    /**
     * GET /admin/events/{slug}/opties
     * List option groups for an event; allow creating/deleting groups.
     */
    public function index(string $slug): void
    {
        $this->requireAuth();
        $pdo   = Database::getInstance($this->config['db']);
        $event = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        $groups = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
        // Attach items to each group
        foreach ($groups as &$group) {
            $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
        }
        unset($group);

        $classes = OcClass::all($pdo);

        View::render('admin/event_options/index', [
            'event'   => $event,
            'groups'  => $groups,
            'classes' => $classes,
            'errors'  => [],
        ]);
    }

    /**
     * POST /admin/events/{slug}/opties
     * Create a new option group for the event.
     */
    public function storeGroup(string $slug): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);
        $event    = Event::findBySlug($pdo, $slug);

        if ($event === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        if (!Csrf::verify()) {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        $name       = trim((string) ($_POST['name']        ?? ''));
        $maxSelect  = max(0, (int) ($_POST['max_select']   ?? 1));
        $isRequired = !empty($_POST['is_required']);
        $sortOrder  = (int) ($_POST['sort_order'] ?? 0);

        $errors = [];
        if ($name === '') {
            $errors[] = 'Naam is verplicht.';
        }

        if ($errors !== []) {
            $groups = EventOptionGroup::findByEvent($pdo, (int) $event['id']);
            foreach ($groups as &$group) {
                $group['items'] = EventOptionItem::findByGroup($pdo, (int) $group['id']);
            }
            unset($group);
            $classes = OcClass::all($pdo);
            View::render('admin/event_options/index', [
                'event'   => $event,
                'groups'  => $groups,
                'classes' => $classes,
                'errors'  => $errors,
            ]);
            return;
        }

        EventOptionGroup::create($pdo, [
            'event_id'   => (int) $event['id'],
            'name'       => $name,
            'max_select' => $maxSelect,
            'is_required'=> $isRequired,
            'sort_order' => $sortOrder,
        ]);

        $this->redirectToIndex($basePath, $slug);
    }

    /**
     * POST /admin/events/{slug}/opties/{group_id}/update
     * Update an option group.
     */
    public function updateGroup(string $slug, int $groupId): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);

        if (!Csrf::verify()) {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        $group = EventOptionGroup::findById($pdo, $groupId);
        if ($group === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        EventOptionGroup::update($pdo, $groupId, [
            'name'       => trim((string) ($_POST['name']       ?? $group['name'])),
            'max_select' => max(0, (int) ($_POST['max_select']  ?? $group['max_select'])),
            'is_required'=> !empty($_POST['is_required']),
            'sort_order' => (int) ($_POST['sort_order'] ?? $group['sort_order']),
        ]);

        $this->redirectToIndex($basePath, $slug);
    }

    /**
     * POST /admin/events/{slug}/opties/{group_id}/delete
     * Delete an option group and all its items.
     */
    public function deleteGroup(string $slug, int $groupId): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);

        if (!Csrf::verify()) {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        EventOptionGroup::delete($pdo, $groupId);
        $this->redirectToIndex($basePath, $slug);
    }

    /**
     * POST /admin/events/{slug}/opties/{group_id}/items
     * Create a new option item in a group.
     */
    public function storeItem(string $slug, int $groupId): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);

        if (!Csrf::verify()) {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        $group = EventOptionGroup::findById($pdo, $groupId);
        if ($group === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        $name           = trim((string) ($_POST['name']       ?? ''));
        $sortOrder      = (int) ($_POST['sort_order'] ?? 0);
        $price          = max(0.0, (float) str_replace(',', '.', (string) ($_POST['price'] ?? '0')));
        [$minClassRank, $maxClassRank] = self::resolveClassRanks(
            (int) ($_POST['min_class_rank'] ?? 0),
            (int) ($_POST['max_class_rank'] ?? 0)
        );

        if ($name !== '') {
            EventOptionItem::create($pdo, [
                'group_id'       => $groupId,
                'name'           => $name,
                'min_grade'      => 1,
                'max_grade'      => 6,
                'sort_order'     => $sortOrder,
                'price'          => $price,
                'min_class_rank' => $minClassRank,
                'max_class_rank' => $maxClassRank,
            ]);
        }

        $this->redirectToIndex($basePath, $slug);
    }

    /**
     * POST /admin/events/{slug}/opties/{group_id}/items/{item_id}/delete
     * Delete an option item.
     */
    public function deleteItem(string $slug, int $groupId, int $itemId): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);

        if (!Csrf::verify()) {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        EventOptionItem::delete($pdo, $itemId);
        $this->redirectToIndex($basePath, $slug);
    }

    /**
     * POST /admin/events/{slug}/opties/{group_id}/items/{item_id}/update
     * Update an existing option item.
     */
    public function updateItem(string $slug, int $groupId, int $itemId): void
    {
        $this->requireAuth();
        $basePath = $this->config['base_path'] ?? '';
        $pdo      = Database::getInstance($this->config['db']);

        if (!Csrf::verify()) {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        $item = EventOptionItem::findById($pdo, $itemId);
        if ($item === null) {
            http_response_code(404);
            View::render('errors/404', []);
            return;
        }

        $name      = trim((string) ($_POST['name']      ?? ''));
        $sortOrder = (int) ($_POST['sort_order']  ?? $item['sort_order']);
        $price     = max(0.0, (float) str_replace(',', '.', (string) ($_POST['price'] ?? (string) $item['price'])));
        [$minClassRank, $maxClassRank] = self::resolveClassRanks(
            (int) ($_POST['min_class_rank'] ?? 0),
            (int) ($_POST['max_class_rank'] ?? 0)
        );

        if ($name === '') {
            $this->redirectToIndex($basePath, $slug);
            return;
        }

        EventOptionItem::update($pdo, $itemId, [
            'name'           => $name,
            'min_grade'      => (int) ($item['min_grade'] ?? 1),
            'max_grade'      => (int) ($item['max_grade'] ?? 6),
            'sort_order'     => $sortOrder,
            'price'          => $price,
            'min_class_rank' => $minClassRank,
            'max_class_rank' => $maxClassRank,
        ]);

        $this->redirectToIndex($basePath, $slug);
    }

    private function redirectToIndex(string $basePath, string $slug): never
    {
        header('Location: ' . $basePath . '/admin/events/' . rawurlencode($slug) . '/opties');
        exit;
    }

    /**
     * Normalise min/max class rank values from POST input.
     * - min and max are both floored at 0.
     * - When max > 0, it is raised to at least min to prevent an inverted range.
     * - max = 0 means "no upper limit" and is not constrained.
     *
     * @return array{0: int, 1: int}
     */
    private static function resolveClassRanks(int $minClassRank, int $maxClassRank): array
    {
        $min = max(0, $minClassRank);
        $max = ($maxClassRank > 0) ? max($min, $maxClassRank) : 0;
        return [$min, $max];
    }
}
