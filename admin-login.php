<?php
declare(strict_types=1);

require __DIR__ . '/app/app.php';
require __DIR__ . '/app/layout.php';

if (($_SESSION['role'] ?? null) === 'admin') {
    redirect('admin.php');
}

$email = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $email = strtolower(trim(post('email')));
    $password = post('password');
    [$adminEmail, $hash] = adminCredentials();
    if ($adminEmail !== '' && $hash !== '' && strlen($password) <= 72
        && password_verify($password, $hash) && $email === $adminEmail) {
        signIn('admin');
        redirect('admin.php');
    }
    $error = 'Enter a Valid Admin Email and Password.';
}

pageStart('Admin Sign-In', 'admin-auth');
?>
<main class="admin-login-shell">
    <section class="admin-intro d-flex flex-column">
        <?php brand(name: 'Gapcorn Industries'); ?>
        <div class="admin-intro-content">
            <h1>Manage Customers and Rewards</h1>
        </div>
    </section>
    <section class="admin-login-form" aria-labelledby="admin-login-heading">
        <div class="auth-card">
            <h2 id="admin-login-heading">Admin Sign-In</h2>
            <?php if (($_GET['expired'] ?? '') === '1'): ?>
                <p class="alert alert-info" role="status">Your Session Expired. Sign In Again.</p>
            <?php endif; ?>
            <?php if ($error !== ''): ?><p class="alert alert-danger" role="alert"><?= e($error) ?></p><?php endif; ?>
            <form method="post">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label for="admin-email" class="form-label">Email Address</label>
                    <input id="admin-email" name="email" type="email" class="form-control" placeholder="admin@example.com"
                           autocomplete="username" maxlength="254" value="<?= e($email) ?>" required>
                </div>
                <div class="mb-4">
                    <label for="admin-password" class="form-label">Password</label>
                    <input id="admin-password" name="password" type="password" class="form-control" placeholder="Enter Your Password"
                           autocomplete="current-password" maxlength="72" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Sign In <?= icon('arrow', 17) ?></button>
            </form>
        </div>
    </section>
</main>
<?php pageEnd(); ?>
