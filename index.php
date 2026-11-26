<?php
declare(strict_types=1);

require __DIR__ . '/app/app.php';
require __DIR__ . '/app/layout.php';

if (($_SESSION['role'] ?? null) === 'customer') {
    redirect('rewards.php');
}

$register = ($_GET['mode'] ?? null) === 'join';
$email = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $email = strtolower(trim(post('email')));
    $password = post('password');

    if ($register && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254)) {
        $error = 'Enter a valid email address.';
    } elseif ($register && (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0"))) {
        $error = 'Use a password between 8 and 72 bytes.';
    } elseif ($register && $email === ADMIN_EMAIL) {
        $error = 'Use a different email address to create a customer account.';
    } elseif ($register) {
        $db = database();
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        try {
            $customer = writeWithCurrentReward($db, static function (array $reward) use ($db, $email, $passwordHash): array {
                $statement = $db->prepare('
                    INSERT INTO customers (email, password_hash, points, reward_id) VALUES (?, ?, ?, ?)
                ');
                $statement->execute([$email, $passwordHash, $reward['points'], $reward['id']]);
                return ['id' => (int) $db->lastInsertId(), 'points' => (int) $reward['points']];
            });
            signIn('customer', ['customer_id' => $customer['id'], 'welcome_points' => $customer['points']]);
            redirect('rewards.php');
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) !== 1062) {
                throw $exception;
            }
            $error = 'An account already uses this email. Sign in to view your points.';
        }
    } else {
        $statement = database()->prepare('SELECT id, password_hash FROM customers WHERE email = :email');
        $statement->execute(['email' => $email]);
        $customer = $statement->fetch();
        if ($customer && strlen($password) <= 72 && password_verify($password, $customer['password_hash'])) {
            signIn('customer', ['customer_id' => $customer['id']]);
            redirect('rewards.php');
        }
        $error = 'Enter the email and password for your customer account.';
    }
}

$title = $register ? 'Create a customer account' : 'Customer sign-in';
pageStart($title, 'customer-page');
?>
<header class="customer-header"><div class="customer-header-inner"><?php brand('Store rewards'); ?></div></header>
<main class="customer-main">
    <div class="customer-card">
        <span class="customer-gift"><?= icon('gift', 28) ?></span>
        <h1><?= e($title) ?></h1>
        <p class="muted"><?= $register ? 'Create your account to sign in and collect your welcome points.' : 'Sign in to see your Gapcorn points balance.' ?></p>
        <?php if ($error !== ''): ?><p class="alert alert-danger" role="alert"><?= e($error) ?></p><?php endif; ?>
        <form method="post">
            <?= csrfField() ?>
            <div class="mb-3">
                <label for="customer-email" class="form-label">Email address</label>
                <input type="email" id="customer-email" name="email" class="form-control"
                       placeholder="you@example.com" autocomplete="username" maxlength="254" value="<?= e($email) ?>" required>
            </div>
            <div class="mb-4">
                <label for="customer-password" class="form-label">Password</label>
                <input type="password" id="customer-password" name="password" class="form-control"
                       placeholder="Enter your password" maxlength="72" <?= $register ? 'minlength="8" aria-describedby="password-hint"' : '' ?>
                       autocomplete="<?= $register ? 'new-password' : 'current-password' ?>" required>
                <?php if ($register): ?><div id="password-hint" class="form-text">Use at least 8 characters.</div><?php endif; ?>
            </div>
            <button class="btn btn-customer w-100" type="submit"><?= $register ? 'Create account and sign in' : 'Sign in' ?> <?= icon('arrow', 17) ?></button>
        </form>
        <div class="customer-switch">
            <?php if ($register): ?>
                Already a customer? <a href="index.php">Sign in</a>
            <?php else: ?>
                New to Gapcorn? <a href="index.php?mode=join">Create an account</a>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php pageEnd(); ?>
