<?php
declare(strict_types=1);

require __DIR__ . '/app/app.php';
require __DIR__ . '/app/layout.php';

if (($_SESSION['role'] ?? null) !== 'admin') {
    redirect('admin-login.php');
}

$rewardPage = 'admin.php?view=rewards';
$showRewards = ($_GET['view'] ?? null) === 'rewards';
$purchaseRewardPage = 'admin.php?view=purchase-rewards';
$showPurchaseRewards = ($_GET['view'] ?? null) === 'purchase-rewards';
$customerPage = 'admin.php?view=customers';
$showCustomers = ($_GET['view'] ?? null) === 'customers';
$error = '';
$amount = '';
$campaignName = post('campaign_name');
$campaignStart = post('starts_on');
$campaignExpiry = post('expires_on');
$effectiveDate = post('effective_date');
$effectiveTime = post('effective_time');
$effectiveDefault = post('default_effective_at');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = post('action');
    if ($action === 'logout') {
        signOut('admin-login.php');
    }
    if (!in_array($action, ['update-reward', 'create-purchase-campaign', 'update-customer'], true)) {
        http_response_code(400);
        exit('Invalid action.');
    }
    $showCustomers = $action === 'update-customer';
    $showRewards = $action === 'update-reward';
    $showPurchaseRewards = $action === 'create-purchase-campaign';
}

$db = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = post('points');
    $points = filter_var($amount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => MAX_POINTS]]);
    if ($points === false) {
        $error = 'Enter a whole number between 0 and ' . number_format(MAX_POINTS) . '.';
    } elseif ($showCustomers) {
        if (!preg_match('/\A([1-9]\d*)\z/', post('customer'), $match)) {
            $error = 'Choose a customer from the directory.';
        } else {
            $statement = $db->prepare("UPDATE customers SET points = :points WHERE id = :id");
            $statement->bindValue(':points', $points, PDO::PARAM_INT);
            $statement->bindValue(':id', $match[0], PDO::PARAM_STR);
            $statement->execute();
            if ($statement->rowCount() === 1) {
                $_SESSION['admin_notice'] = 'Customer balance saved at ' . number_format($points) . ' points.';
                redirect($customerPage);
            }
            $error = 'This customer could not be found.';
        }
    } elseif ($showPurchaseRewards) {
        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata'));
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $campaignStart, new DateTimeZone('Asia/Kolkata'));
        $expiry = DateTimeImmutable::createFromFormat('!Y-m-d', $campaignExpiry, new DateTimeZone('Asia/Kolkata'));
        if (strlen($campaignName) < 3 || strlen($campaignName) > 120) {
            $error = 'Enter a campaign name between 3 and 120 characters.';
        } elseif ($start === false || $start->format('Y-m-d') !== $campaignStart || $campaignStart !== $today->format('Y-m-d')) {
            $error = 'The campaign start date must be today.';
        } elseif ($expiry === false || $expiry->format('Y-m-d') !== $campaignExpiry || $expiry < $today) {
            $error = 'Choose an expiry date on or after today.';
        } else {
            $statement = $db->prepare('INSERT INTO purchase_reward_campaigns (name, points, starts_on, expires_on) VALUES (?, ?, ?, ?)');
            $statement->execute([$campaignName, $points, $campaignStart, $campaignExpiry]);
            $_SESSION['admin_notice'] = $campaignName . ' created with ' . number_format($points) . ' reward points.';
            redirect($purchaseRewardPage);
        }
    } else {
        $effectiveAt = null;
        $effectiveInput = $effectiveDate . 'T' . $effectiveTime;
        if ($effectiveInput !== $effectiveDefault || $effectiveDate === '' || $effectiveTime === '') {
            $selected = preg_match('/\A\d{4}-\d{2}-\d{2}T\d{2}:\d{2}\z/', $effectiveInput)
                ? DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $effectiveInput, new DateTimeZone('Asia/Kolkata'))
                : false;
            if ($selected === false || $selected->format('Y-m-d\TH:i') !== $effectiveInput || $selected <= new DateTimeImmutable()) {
                $error = 'Choose a future date and time in IST, or reopen the dialog to select the current time.';
            } else {
                $effectiveAt = $selected->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s.v');
            }
        }

        if ($error === '') {
            $saveReward = static function (array $current) use ($db, $points, $effectiveAt): bool {
                $table = 'reward_settings';
                if ($effectiveAt !== null) {
                    $statement = $db->prepare("
                        INSERT INTO $table (points, effective_at)
                        SELECT ?, ? WHERE CAST(? AS DATETIME(3)) > UTC_TIMESTAMP(3)
                    ");
                    $statement->execute([$points, $effectiveAt, $effectiveAt]);
                    return $statement->rowCount() === 1;
                }
                if ((int) $current['points'] === $points) {
                    return false;
                }
                $statement = $db->prepare("INSERT INTO $table (points) VALUES (?)");
                $statement->execute([$points]);
                return true;
            };
            $changed = writeWithCurrentReward($db, $saveReward);

            if ($effectiveAt !== null && !$changed) {
                $error = 'That time has passed. Choose another date and time in IST.';
            } else {
                $formattedPoints = number_format($points);
                $rewardName = 'first sign-in reward';
                $_SESSION['admin_notice'] = $effectiveAt !== null
                    ? "The $formattedPoints-point $rewardName is scheduled for " . displayTime($effectiveAt) . ' IST.'
                    : ($changed
                        ? "The $rewardName is now $formattedPoints points."
                        : "The $rewardName is already $formattedPoints points.");
                redirect($rewardPage);
            }
        }
    }
}

$notice = $_SESSION['admin_notice'] ?? '';
unset($_SESSION['admin_notice']);

if ($showCustomers) {
    $customers = $db->query('SELECT id, email, points, rewarded_at AS joined_at,
        COALESCE(last_active_at, rewarded_at) AS last_active_at
        FROM customers ORDER BY joined_at DESC, id DESC')->fetchAll();
} elseif ($showRewards) {
    $db->beginTransaction();
    $settings = $db->query('SELECT id, points, effective_at FROM reward_settings ORDER BY effective_at DESC, id DESC')->fetchAll();
    if ($error === '') {
        $effectiveDefault = (new DateTimeImmutable('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d\TH:i');
        [$effectiveDate, $effectiveTime] = explode('T', $effectiveDefault, 2);
    }
    $current = currentReward($db);
    $db->commit();
} elseif ($showPurchaseRewards) {
    $campaigns = $db->query('SELECT id, name, points, starts_on, expires_on, created_at FROM purchase_reward_campaigns ORDER BY starts_on DESC, id DESC')->fetchAll();
    if ($error === '') {
        $campaignStart = (new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
    }
}

if (!$showCustomers && !$showRewards && !$showPurchaseRewards) {
    $registeredCount = (int) $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
}
$title = $showCustomers ? 'Customers' : ($showRewards ? 'Welcome Reward' : ($showPurchaseRewards ? 'Purchase Reward' : 'Dashboard'));
pageStart($title, 'dashboard');
?>
<div class="workspace">
    <aside class="offcanvas offcanvas-start sidebar" id="admin-sidebar" aria-label="Admin navigation" tabindex="-1">
        <div class="sidebar-brand">
            <?php brand('Admin'); ?>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"
                    data-bs-target="#admin-sidebar" aria-label="Close navigation"></button>
        </div>
        <nav class="sidebar-nav" aria-label="Workspace">
            <a class="side-link<?= !$showCustomers && !$showRewards && !$showPurchaseRewards ? ' active' : '' ?>" href="admin.php"
               <?= !$showCustomers && !$showRewards && !$showPurchaseRewards ? 'aria-current="page"' : '' ?>><?= icon('dashboard', 19) ?> Dashboard</a>
            <h2 class="section-label">Modules</h2>
            <a class="side-link<?= $showCustomers ? ' active' : '' ?>" href="<?= e($customerPage) ?>"
               <?= $showCustomers ? 'aria-current="page"' : '' ?>><?= icon('customers', 19) ?> Customers</a>
            <h2 class="section-label mt-4">Reward Management</h2>
            <a class="side-link<?= $showRewards ? ' active' : '' ?>" href="<?= e($rewardPage) ?>"
               <?= $showRewards ? 'aria-current="page"' : '' ?>><?= icon('gift', 19) ?> Welcome Reward</a>
            <a class="side-link<?= $showPurchaseRewards ? ' active' : '' ?>" href="<?= e($purchaseRewardPage) ?>"
               <?= $showPurchaseRewards ? 'aria-current="page"' : '' ?>><?= icon('sales', 19) ?> Purchase Reward</a>
        </nav>
        <form method="post" class="mt-auto flex-shrink-0 border-top px-3 py-2">
            <?= csrfField() ?>
            <button type="submit" name="action" value="logout" class="side-link mb-0"><?= icon('logout', 19) ?> Logout</button>
        </form>
    </aside>
    <div class="workspace-main">
        <header class="workspace-header">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn menu-button d-lg-none" data-bs-toggle="offcanvas"
                        data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Open navigation"><?= icon('menu') ?></button>
                <div>
                    <span class="section-label d-block"><?= $showRewards || $showPurchaseRewards ? 'Reward Management' : ($showCustomers ? 'Modules' : 'Store administration') ?></span>
                    <span class="header-title"><?= e($title) ?></span>
                </div>
            </div>
            <span class="admin-identity"><span class="avatar" aria-hidden="true">A</span> <span class="d-none d-sm-inline">Administrator</span></span>
        </header>
        <main class="dashboard-content">
            <?php if ($notice !== ''): ?>
                <div class="alert alert-success" role="status"><?= icon('check', 18) ?> <?= e($notice) ?></div>
            <?php endif; ?>
            <?php require __DIR__ . ($showCustomers ? '/views/admin-customers-view.php' : ($showRewards ? '/views/admin-rewards-view.php' : ($showPurchaseRewards ? '/views/admin-purchase-rewards-view.php' : '/views/admin-dashboard-view.php'))); ?>
        </main>
    </div>
</div>
<?php
if ($showRewards) {
    require __DIR__ . '/views/admin-reward-dialog.php';
} elseif ($showPurchaseRewards) {
    require __DIR__ . '/views/admin-purchase-campaign-dialog.php';
}
pageEnd();
?>
