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
$ordersPage = 'admin.php?view=orders';
$showOrders = ($_GET['view'] ?? null) === 'orders';
$error = '';
$amount = '';
$campaignName = post('campaign_name');
$campaignStart = post('starts_on');
$campaignExpiry = post('expires_on');
$campaignExpiryTime = post('expires_time');
$campaignNow = new DateTimeImmutable('now', new DateTimeZone('Asia/Kolkata'));
$effectiveDate = post('effective_date');
$effectiveTime = post('effective_time');
$effectiveDefault = post('default_effective_at');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $action = post('action');
    if ($action === 'logout') {
        signOut('admin-login.php');
    }
    if (!in_array($action, ['update-reward', 'create-purchase-campaign', 'set-campaign-status', 'extend-campaign-expiry', 'update-customer-details', 'update-product'], true)) {
        http_response_code(400);
        exit('Invalid Action.');
    }
    $showCustomers = false;
    $showOrders = false;
    $showRewards = $action === 'update-reward';
    $showPurchaseRewards = $action === 'create-purchase-campaign';
}

$db = database();
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update-product') {
    require __DIR__ . '/app/admin-product-update.php';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'update-customer-details') {
    $id = filter_var(post('customer_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) {
        http_response_code(400);
        exit('Invalid Customer.');
    }
    $name = trim(post('name'));
    $email = strtolower(trim(post('email')));
    $address = trim(post('address'));
    [$adminEmail] = adminCredentials();
    if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL) || $email === $adminEmail) {
        $error = 'Enter a Valid Customer Email Address.';
    } elseif (preg_match('/\A.{0,120}\z/us', $name) !== 1 || preg_match('/\A.{0,1000}\z/us', $address) !== 1) {
        $error = 'Use Up to 120 Characters for the Name and 1,000 for the Address.';
    } else {
        try {
            $statement = $db->prepare('UPDATE customers SET name = ?, email = ?, address = ? WHERE id = ?');
            $statement->execute([$name === '' ? null : $name, $email, $address === '' ? null : $address, $id]);
            if ($statement->rowCount() > 0) {
                $_SESSION['admin_notice'] = 'Customer Details Saved.';
                redirect($customerPage);
            }
            $error = 'No Changes Were Saved. Check That the Customer Still Exists.';
        } catch (PDOException $exception) {
            if ((int) ($exception->errorInfo[1] ?? 0) !== 1062) {
                throw $exception;
            }
            $error = 'Another Customer Already Uses This Email Address.';
        }
    }
    $_SESSION['customer_edit_error'] = ['id' => $id, 'message' => $error, 'name' => $name, 'email' => $email, 'address' => $address];
    redirect($customerPage . '&edit=' . $id);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'extend-campaign-expiry') {
    $campaignId = filter_var(post('campaign_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $input = $campaignExpiry . ' ' . $campaignExpiryTime;
    $expiry = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $input, $campaignNow->getTimezone());
    if ($campaignId === false || $expiry === false || $expiry->format('Y-m-d H:i') !== $input || $expiry <= $campaignNow) {
        $_SESSION['campaign_error'] = 'Choose a Future Expiry Date and Time in IST.';
    } else {
        $statement = $db->prepare('UPDATE purchase_reward_campaigns SET expires_on = ?, expires_time = ?
            WHERE id = ? AND TIMESTAMP(expires_on, expires_time) < ?');
        $statement->execute([$campaignExpiry, $expiry->format('H:i:s'), $campaignId, $expiry->format('Y-m-d H:i:s')]);
        if ($statement->rowCount() > 0) {
            $_SESSION['admin_notice'] = 'Campaign Expiry Extended.';
        } else {
            $_SESSION['campaign_error'] = 'Choose an Expiry Later Than the Current Deadline for an Existing Campaign.';
        }
    }
    redirect($purchaseRewardPage);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'set-campaign-status') {
    $campaignId = filter_var(post('campaign_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $enabled = post('enabled');
    if ($campaignId === false || !in_array($enabled, ['0', '1'], true)) {
        http_response_code(400);
        exit('Invalid Campaign Status.');
    }
    $statement = $db->prepare('UPDATE purchase_reward_campaigns SET is_enabled = ?
        WHERE id = ? AND TIMESTAMP(expires_on, expires_time) > ?');
    $statement->execute([(int) $enabled, $campaignId, $campaignNow->format('Y-m-d H:i:s')]);
    $_SESSION['admin_notice'] = $statement->rowCount() > 0
        ? ($enabled === '1' ? 'Campaign Activated.' : 'Campaign Deactivated.')
        : 'This Campaign Has Expired or Is Unavailable.';
    redirect($purchaseRewardPage);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = post('points');
    $points = filter_var($amount, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => MAX_POINTS]]);
    if ($points === false) {
        $error = 'Enter a Whole Number Between 0 and ' . number_format(MAX_POINTS) . '.';
    } elseif ($showPurchaseRewards) {
        $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata'));
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $campaignStart, new DateTimeZone('Asia/Kolkata'));
        $expiryInput = $campaignExpiry . ' ' . $campaignExpiryTime;
        $expiry = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $expiryInput, $campaignNow->getTimezone());
        if (strlen($campaignName) < 3 || strlen($campaignName) > 120) {
            $error = 'Enter a Campaign Name Between 3 and 120 Characters.';
        } elseif ($start === false || $start->format('Y-m-d') !== $campaignStart || $campaignStart !== $today->format('Y-m-d')) {
            $error = 'The Campaign Start Date Must Be Today.';
        } elseif ($expiry === false || $expiry->format('Y-m-d H:i') !== $expiryInput || $expiry <= $campaignNow) {
            $error = 'Choose a Future Expiry Date and Time in IST.';
        } else {
            $statement = $db->prepare('INSERT INTO purchase_reward_campaigns (name, points, starts_on, expires_on, expires_time) VALUES (?, ?, ?, ?, ?)');
            $statement->execute([$campaignName, $points, $campaignStart, $campaignExpiry, $expiry->format('H:i:s')]);
            $_SESSION['admin_notice'] = $campaignName . ' Created with ' . number_format($points) . ' Reward Points.';
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
                $error = 'Choose a Future Date and Time in IST, or Reopen the Dialog to Select the Current Time.';
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
                $error = 'That Time Has Passed. Choose Another Date and Time in IST.';
            } else {
                $formattedPoints = number_format($points);
                $rewardName = 'First Sign-In Reward';
                $_SESSION['admin_notice'] = $effectiveAt !== null
                    ? "The $formattedPoints-point $rewardName Is Scheduled for " . displayTime($effectiveAt) . ' IST.'
                    : ($changed
                        ? "The $rewardName Is Now $formattedPoints Points."
                        : "The $rewardName Is Already $formattedPoints Points.");
                redirect($rewardPage);
            }
        }
    }
}

$notice = $_SESSION['admin_notice'] ?? '';
unset($_SESSION['admin_notice']);

if ($showCustomers) {
    $editId = filter_var($_GET['edit'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $editCustomer = null;
    $editError = '';
    $attempt = $_SESSION['customer_edit_error'] ?? null;
    unset($_SESSION['customer_edit_error']);
    if ($editId !== false) {
        $statement = $db->prepare('SELECT id, name, email, address FROM customers WHERE id = ?');
        $statement->execute([$editId]);
        $editCustomer = $statement->fetch();
        if (!$editCustomer) {
            $editError = 'Customer Could Not Be Found.';
        } elseif (($attempt['id'] ?? null) === $editId) {
            $editError = $attempt['message'];
            $editCustomer = $attempt + $editCustomer;
        }
    }
    $customers = $db->query("SELECT id, name, email, phone, address, points, rewarded_at AS joined_at,
        COALESCE(last_active_at, rewarded_at) AS last_active_at,
        COALESCE((SELECT GROUP_CONCAT(cp.product_name ORDER BY cp.purchased_at DESC SEPARATOR ', ')
            FROM customer_purchases cp WHERE cp.customer_id = customers.id), '') AS products_purchased
        FROM customers ORDER BY joined_at DESC, id DESC")->fetchAll();
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
    $campaigns = $db->query('SELECT id, name, points, starts_on, created_at, expires_on, expires_time, is_enabled FROM purchase_reward_campaigns ORDER BY starts_on DESC, id DESC')->fetchAll();
    if ($error === '') {
        $campaignStart = (new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
    }
} elseif ($showOrders) {
    require __DIR__ . '/app/admin-orders.php';
} else {
    $registeredCount = (int) $db->query('SELECT COUNT(*) FROM customers')->fetchColumn();
}
$isDashboard = !$showCustomers && !$showRewards && !$showPurchaseRewards && !$showOrders;
$title = match (true) {
    $showCustomers => 'Customers',
    $showRewards => 'Welcome Reward',
    $showPurchaseRewards => 'Purchase Reward',
    $showOrders => 'Orders',
    default => 'Dashboard',
};
$sectionTitle = match (true) {
    $showRewards || $showPurchaseRewards => 'Reward Management',
    $showCustomers => 'Modules',
    $showOrders => 'Order Management',
    default => 'Gapcorn Industries',
};
pageStart($title, 'dashboard');
?>
<div class="workspace">
    <aside class="offcanvas offcanvas-start sidebar" id="admin-sidebar" aria-label="Admin Navigation" tabindex="-1">
        <div class="sidebar-brand">
            <?php brand('Admin', name: 'Gapcorn Industries'); ?>
            <button type="button" class="btn-close d-lg-none" data-bs-dismiss="offcanvas"
                    data-bs-target="#admin-sidebar" aria-label="Close Navigation"></button>
        </div>
        <nav class="sidebar-nav" aria-label="Workspace">
            <a class="side-link<?= $isDashboard ? ' active' : '' ?>" href="admin.php"
               <?= $isDashboard ? 'aria-current="page"' : '' ?>><?= icon('dashboard', 19) ?> Dashboard</a>
            <h2 class="section-label">Modules</h2>
            <a class="side-link<?= $showCustomers ? ' active' : '' ?>" href="<?= e($customerPage) ?>"
               <?= $showCustomers ? 'aria-current="page"' : '' ?>><?= icon('customers', 19) ?> Customers</a>
            <h2 class="section-label mt-4">Reward Management</h2>
            <a class="side-link<?= $showRewards ? ' active' : '' ?>" href="<?= e($rewardPage) ?>"
               <?= $showRewards ? 'aria-current="page"' : '' ?>><?= icon('gift', 19) ?> Welcome Reward</a>
            <a class="side-link<?= $showPurchaseRewards ? ' active' : '' ?>" href="<?= e($purchaseRewardPage) ?>"
               <?= $showPurchaseRewards ? 'aria-current="page"' : '' ?>><?= icon('sales', 19) ?> Purchase Reward</a>
            <h2 class="section-label mt-4">Order Management</h2>
            <a class="side-link<?= $showOrders ? ' active' : '' ?>" href="<?= e($ordersPage) ?>"
               <?= $showOrders ? 'aria-current="page"' : '' ?>><?= icon('orders', 19) ?> Orders</a>
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
                        data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Open Navigation"><?= icon('menu') ?></button>
                <div>
                    <span class="section-label d-block"><?= e($sectionTitle) ?></span>
                    <span class="header-title"><?= e($title) ?></span>
                </div>
            </div>
            <span class="admin-identity"><span class="avatar" aria-hidden="true">A</span> <span class="d-none d-sm-inline">Administrator</span></span>
        </header>
        <main class="dashboard-content">
            <?php if ($notice !== ''): ?>
                <div class="alert alert-success" role="status"><?= icon('check', 18) ?> <?= e($notice) ?></div>
            <?php endif; ?>
            <?php require __DIR__ . match (true) {
                $showCustomers => '/views/admin-customers-view.php',
                $showRewards => '/views/admin-rewards-view.php',
                $showPurchaseRewards => '/views/admin-purchase-rewards-view.php',
                $showOrders => '/views/admin-orders-view.php',
                default => '/views/admin-dashboard-view.php',
            }; ?>
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
