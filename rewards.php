<?php
declare(strict_types=1);

require __DIR__ . '/app/app.php';
require __DIR__ . '/app/layout.php';

if (($_SESSION['role'] ?? null) !== 'customer') {
    redirect('index.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    signOut('index.php');
}

$db = database();
$activity = $db->prepare("
    UPDATE customers SET last_active_at = UTC_TIMESTAMP(3)
    WHERE id = :id AND (last_active_at IS NULL OR last_active_at < UTC_TIMESTAMP(3) - INTERVAL 1 MINUTE)
");
$activity->execute(['id' => $_SESSION['customer_id']]);
$statement = $db->prepare('SELECT email, points FROM customers WHERE id = :id');
$statement->execute(['id' => $_SESSION['customer_id']]);
$customer = $statement->fetch();
if (!$customer) {
    signOut('index.php');
}
$welcomePoints = $_SESSION['welcome_points'] ?? null;
unset($_SESSION['welcome_points']);

pageStart('My rewards', 'customer-page');
?>
<header class="customer-header">
    <div class="customer-header-inner">
        <?php brand('Store rewards'); ?>
        <form method="post">
            <?= csrfField() ?>
            <button class="btn btn-outline-secondary btn-sm" type="submit"><?= icon('logout', 16) ?> Logout</button>
        </form>
    </div>
</header>
<main class="customer-main">
    <div class="customer-card text-center">
        <span class="customer-gift"><?= icon('gift', 28) ?></span>
        <h1 id="rewards-heading" tabindex="-1">My rewards</h1>
        <p class="muted text-break"><?= e($customer['email']) ?></p>
        <div class="customer-balance">
            <span>Points balance</span>
            <strong><?= number_format($customer['points']) ?></strong>
        </div>
    </div>
</main>
<?php
if ($welcomePoints !== null) {
    require __DIR__ . '/views/welcome-reward.php';
}
pageEnd();
?>
