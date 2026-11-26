<?php
declare(strict_types=1);

function icon(string $name, int $size = 20): string
{
    $path = match ($name) {
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
        'sales' => '<path d="M3 4h2l2.2 10h11.5l2-7H6"/><circle cx="9" cy="19" r="1.25"/><circle cx="18" cy="19" r="1.25"/>',
        'revenue' => '<path d="M4 19V5m0 14h16M8 14l4-4 3 2 5-5m-4 0h4v4"/>',
        'orders' => '<path d="M12 3 4 7.5v9l8 4.5 8-4.5v-9L12 3Zm0 9 8-4.5M12 12 4 7.5m8 4.5v9M8 5.25l8 4.5"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="5.5"/><path d="m15 15 4 4"/>',
        'brand' => '<path d="M20 13.5V8.2a2 2 0 0 0-1-1.74l-6-3.43a2 2 0 0 0-2 0L5 6.46A2 2 0 0 0 4 8.2v7.6a2 2 0 0 0 1 1.74l6 3.43a2 2 0 0 0 2 0l4.5-2.57"/><path d="M12 12.25 4.4 7.9M12 12.25l7.6-4.35M12 12.25V21m5-5 2 2 3-3"/>',
        'customers' => '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m20 0v-2a4 4 0 0 0-3-3.9M16 3.1a4 4 0 0 1 0 7.8"/><circle cx="9" cy="7" r="4"/>',
        'logout' => '<path d="M10 17l5-5-5-5m5 5H3m11-8h5a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-5"/>',
        'gift' => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M5 12v8h14v-8M12 8v12m0-12H8a2.5 2.5 0 1 1 2.5-2.5L12 8Zm0 0h4a2.5 2.5 0 1 0-2.5-2.5L12 8Z"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'edit' => '<path d="m14 5 5 5M4 20l4.5-1 12-12a1.5 1.5 0 0 0 0-2.1l-1.4-1.4a1.5 1.5 0 0 0-2.1 0L5 15.5 4 20Z"/>',
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'arrow' => '<path d="M4 12h15m-6-6 6 6-6 6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
    };
    return '<svg width="' . $size . '" height="' . $size . '" viewBox="0 0 24 24" fill="none" '
        . 'stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . $path . '</svg>';
}

function hasAssetBundle(): bool
{
    $dist = dirname(__DIR__) . '/assets/dist/';
    return is_file($dist . 'app.css') && is_file($dist . 'app.js')
        && is_file($dist . 'demo-customer-avatars.jpg');
}

function pageStart(string $title, string $bodyClass): void
{
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title><?= e($title) ?> | Gapcorn</title>
        <?php if (hasAssetBundle()): ?>
            <link rel="stylesheet" href="assets/dist/app.css?v=<?= filemtime(dirname(__DIR__) . '/assets/dist/app.css') ?>">
        <?php else: ?>
            <link rel="stylesheet" href="assets/vendor/bootstrap-5.0.2/bootstrap.min.css">
            <link rel="stylesheet" href="assets/app.css?v=<?= filemtime(dirname(__DIR__) . '/assets/app.css') ?>">
        <?php endif; ?>
    </head>
    <body class="<?= e($bodyClass) ?>">
    <?php
}

function brand(string $subtitle = ''): void
{
    ?>
    <div class="brand">
        <span class="brand-mark"><?= icon('brand', 22) ?></span>
        <span>Gapcorn<?php if ($subtitle !== ''): ?><small><?= e($subtitle) ?></small><?php endif; ?></span>
    </div>
    <?php
}

function pageEnd(): void
{
    ?>
    <?php if (hasAssetBundle()): ?>
        <script src="assets/dist/app.js?v=<?= filemtime(dirname(__DIR__) . '/assets/dist/app.js') ?>"></script>
    <?php else: ?>
        <script src="assets/vendor/bootstrap-5.0.2/bootstrap.bundle.min.js"></script>
        <script src="assets/app.js?v=<?= filemtime(dirname(__DIR__) . '/assets/app.js') ?>"></script>
    <?php endif; ?>
    </body>
    </html>
    <?php
}
