<?php
// app/helpers/functions.php

// ── URL helpers ───────────────────────────────────────────────
function url(string $path = ''): string {
    return APP_URL . '/' . ltrim($path, '/');
}
function admin_url(string $path = ''): string {
    return ADMIN_URL . '/' . ltrim($path, '/');
}
function seller_url(string $path = ''): string {
    return SELLER_URL . '/' . ltrim($path, '/');
}
function admin_asset(string $path): string {
    return ADMIN_ASSETS . '/' . ltrim($path, '/');
}
function frontend_asset(string $path): string {
    return FRONTEND_ASSETS . '/' . ltrim($path, '/');
}
function seller_asset(string $path): string {
    return SELLER_ASSETS . '/' . ltrim($path, '/');
}

// ── Output helpers ────────────────────────────────────────────
function e(mixed $v): string {
    return htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');
}
function currency(float $amount, string $sym = '₹'): string {
    return $sym . number_format($amount, 2);
}

// ── Date helpers ──────────────────────────────────────────────
function formatDate(?string $d, string $fmt = 'd M Y'): string {
    return $d ? date($fmt, strtotime($d)) : '—';
}
function formatDateTime(?string $d): string {
    return formatDate($d, 'd M Y, h:i A');
}
function timeAgo(string $d): string {
    $diff = time() - strtotime($d);
    if ($diff < 60)    return "{$diff}s ago";
    if ($diff < 3600)  return floor($diff / 60) . 'm ago';
    if ($diff < 86400) return floor($diff / 3600) . 'h ago';
    return floor($diff / 86400) . 'd ago';
}

// ── UI helpers ────────────────────────────────────────────────
function statusBadge(string $status): string {
    $map = [
        'active'     => 'success', 'inactive'   => 'secondary',
        'pending'    => 'warning', 'suspended'  => 'danger',
        'rejected'   => 'danger',  'placed'     => 'primary',
        'confirmed'  => 'info',    'processing' => 'warning',
        'shipped'    => 'info',    'delivered'  => 'success',
        'cancelled'  => 'danger',  'returned'   => 'secondary',
        'paid'       => 'success', 'failed'     => 'danger',
        'refunded'   => 'info',    'on_hold'    => 'warning',
        'draft'      => 'secondary',
    ];
    $c = $map[strtolower($status)] ?? 'secondary';
    $l = ucfirst(str_replace('_', ' ', $status));
    return "<span class=\"badge badge-{$c}\">{$l}</span>";
}

/**
 * Renders pagination links, automatically preserving every current
 * GET parameter except `page` (search, status filters, sort, date
 * ranges, etc.) — so paging to page 2 while a filter is active
 * doesn't silently drop the filter. Callers just pass the base path;
 * they don't need to manually thread query strings through.
 */
function paginate(array $pg, string $baseUrl): string {
    if ($pg['total_pages'] <= 1) return '';
    $cur = $pg['current_page'];
    $tot = $pg['total_pages'];

    $params = $_GET;
    unset($params['page']);
    // Strip any query string already present in $baseUrl so we don't
    // duplicate parameters — $params (from $_GET) is authoritative.
    $path = strtok($baseUrl, '?');

    $urlFor = function (int $page) use ($path, $params): string {
        $params['page'] = $page;
        return $path . '?' . http_build_query($params);
    };

    $h   = '<nav><ul class="pagination">';
    $h .= '<li class="page-item' . ($cur <= 1 ? ' disabled' : '') . '">'
        . '<a class="page-link" href="' . e($urlFor(max(1, $cur - 1))) . '">‹</a></li>';
    for ($i = max(1, $cur - 2); $i <= min($tot, $cur + 2); $i++) {
        $h .= '<li class="page-item' . ($i === $cur ? ' active' : '') . '">'
            . '<a class="page-link" href="' . e($urlFor($i)) . '">' . $i . '</a></li>';
    }
    $h .= '<li class="page-item' . ($cur >= $tot ? ' disabled' : '') . '">'
        . '<a class="page-link" href="' . e($urlFor(min($tot, $cur + 1))) . '">›</a></li>';
    return $h . '</ul></nav>';
}

/**
 * Renders a sortable <th>. Clicking toggles asc/desc on that column
 * and preserves every other current GET param (search, filters, etc.)
 * — same "don't lose state" principle as paginate().
 */
function sortHeader(string $label, string $field, string $baseUrl): string {
    $params  = $_GET;
    $curSort = $params['sort'] ?? '';
    $curDir  = $params['dir'] ?? 'asc';
    $nextDir = ($curSort === $field && $curDir === 'asc') ? 'desc' : 'asc';

    $params['sort'] = $field;
    $params['dir']  = $nextDir;
    unset($params['page']); // changing sort order resets to page 1
    $path = strtok($baseUrl, '?');
    $url  = $path . '?' . http_build_query($params);

    $arrow = '';
    if ($curSort === $field) {
        $arrow = $curDir === 'asc' ? ' ↑' : ' ↓';
    }
    return '<a href="' . e($url) . '" class="sort-link">' . e($label) . $arrow . '</a>';
}

/**
 * Whitelists a requested sort field against allowed columns —
 * required before ever interpolating a column name into SQL, since
 * column names can't be parameterized like values can.
 */
function safeSortField(?string $requested, array $allowed, string $default): string {
    return in_array($requested, $allowed, true) ? $requested : $default;
}

function safeSortDir(?string $requested): string {
    return strtolower($requested ?? '') === 'desc' ? 'DESC' : 'ASC';
}

/**
 * Frontend-style pagination (flat `<a class="page-link">` anchors,
 * matching the storefront's existing CSS — not the Bootstrap
 * `<ul><li>` structure the admin panel's paginate() uses). Same
 * state-preservation principle: every current GET param except
 * `page` carries over automatically. Also renders a "Showing X–Y of
 * Z results" summary, which the old inline pagination markup lacked
 * entirely.
 */
function productPagination(array $pg, string $baseUrl, int $perPage = ITEMS_PER_PAGE): string {
    $cur = $pg['current_page'];
    $tot = $pg['total_pages'];
    $total = $pg['total'] ?? 0;

    $params = $_GET;
    unset($params['page']);
    $path = strtok($baseUrl, '?');
    $urlFor = function (int $page) use ($path, $params): string {
        $params['page'] = $page;
        return $path . '?' . http_build_query($params);
    };

    $from = $total > 0 ? (($cur - 1) * $perPage) + 1 : 0;
    $to   = min($cur * $perPage, $total);
    $summary = $total > 0
        ? '<div class="pagination-summary">Showing ' . number_format($from) . '–' . number_format($to) . ' of ' . number_format($total) . ' results</div>'
        : '';

    if ($tot <= 1) return $summary;

    $h = '<div class="pagination">';
    $h .= $cur > 1
        ? '<a href="' . e($urlFor($cur - 1)) . '" class="page-link">‹ Prev</a>'
        : '<span class="page-link disabled">‹ Prev</span>';

    $start = max(1, $cur - 2);
    $end   = min($tot, $cur + 2);
    if ($start > 1) {
        $h .= '<a href="' . e($urlFor(1)) . '" class="page-link">1</a>';
        if ($start > 2) $h .= '<span class="page-ellipsis">…</span>';
    }
    for ($i = $start; $i <= $end; $i++) {
        $h .= $i === $cur
            ? '<span class="page-link active">' . $i . '</span>'
            : '<a href="' . e($urlFor($i)) . '" class="page-link">' . $i . '</a>';
    }
    if ($end < $tot) {
        if ($end < $tot - 1) $h .= '<span class="page-ellipsis">…</span>';
        $h .= '<a href="' . e($urlFor($tot)) . '" class="page-link">' . $tot . '</a>';
    }

    $h .= $cur < $tot
        ? '<a href="' . e($urlFor($cur + 1)) . '" class="page-link">Next ›</a>'
        : '<span class="page-link disabled">Next ›</span>';
    $h .= '</div>';

    return $summary . $h;
}
function isActive(string $segment, string $prefix = ''): string {
    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    $needle = $prefix ? "/{$prefix}/{$segment}" : "/{$segment}";
    return str_contains($uri, $needle) ? 'active' : '';
}
function adminActive(string $segment): string {
    return isActive($segment, ADMIN_PREFIX);
}
function sellerActive(string $segment): string {
    return isActive($segment, SELLER_PREFIX);
}

// ── Utility ───────────────────────────────────────────────────
function slugify(string $t): string {
    return trim(preg_replace('/-+/', '-', preg_replace('/[^a-z0-9-]/', '-', strtolower(trim($t)))), '-');
}

/**
 * Validates and stores an uploaded file. Unlike the original version,
 * this actually checks the file before trusting it:
 *   - Extension must be in the allowed list for the given context
 *     (client-supplied $file['type'] is never trusted — it's spoofable)
 *   - Real MIME type is verified server-side via finfo, and must be
 *     consistent with the extension
 *   - Size is capped
 *   - The stored filename is fully random (not derived from the
 *     original name), so nothing user-controlled ends up in the path
 *
 * $context selects the allowed type set:
 *   'image'    → jpg/jpeg/png/gif/webp (products, banners, brand logos, avatars)
 *   'document' → pdf/jpg/jpeg/png (seller KYC documents)
 */
function uploadFile(array $file, string $folder = 'general', string $context = 'image'): ?string {
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return null;

    $maxBytes = 5 * 1024 * 1024; // 5MB
    if (($file['size'] ?? 0) <= 0 || $file['size'] > $maxBytes) return null;

    $allowed = match ($context) {
        'document' => [
            'pdf'  => 'application/pdf',
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
        ],
        default => [
            'jpg'  => 'image/jpeg', 'jpeg' => 'image/jpeg',
            'png'  => 'image/png',
            'gif'  => 'image/gif',
            'webp' => 'image/webp',
        ],
    };

    $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
    if (!isset($allowed[$ext])) return null;

    // Verify the actual file content, not the client-supplied extension
    // or Content-Type header — both are trivially spoofable.
    if (!is_uploaded_file($file['tmp_name'])) return null;
    $finfo    = finfo_open(FILEINFO_MIME_TYPE);
    $realMime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if ($realMime !== $allowed[$ext]) return null;

    $dir = UPLOAD_PATH . '/' . $folder;
    if (!is_dir($dir)) mkdir($dir, 0755, true);

    $slug = slugify(\App\Frontend\Services\SettingsService::get('site_name', 'Namma E Store'));
    $name = $slug . '-' . bin2hex(random_bytes(16)) . '.' . $ext;
    return move_uploaded_file($file['tmp_name'], $dir . '/' . $name)
        ? $folder . '/' . $name
        : null;
}

/**
 * Versioned asset URL for cache busting: asset('frontend/css/main.css')
 * appends the file's mtime so browsers/service-worker fetch fresh copies
 * whenever the file changes.
 */
function asset(string $path): string
{
    $file = BASE_PATH . '/assets/' . ltrim($path, '/');
    $v    = is_file($file) ? filemtime($file) : time();
    return APP_URL . '/assets/' . ltrim($path, '/') . '?v=' . $v;
}
