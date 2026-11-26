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
    $error = 'Enter a valid admin email and password.';
}

pageStart('Admin sign-in', 'admin-auth');
?>
<main class="admin-login-shell">
    <section class="admin-intro d-flex flex-column">
        <?php brand('Store administration'); ?>
        <div class="admin-intro-content">
            <p class="section-label">Admin workspace</p>
            <h1>Manage customer rewards.</h1>
            <p>Set the first sign-in bonus and see how many points each customer received.</p>
        </div>
    </section>
    <section class="admin-login-form" aria-labelledby="admin-login-heading">
        <div class="auth-card">
            <h2 id="admin-login-heading">Admin sign-in</h2>
            <p class="muted">Enter your admin credentials to access the dashboard.</p>
            <?php if ($error !== ''): ?><p class="alert alert-danger" role="alert"><?= e($error) ?></p><?php endif; ?>
            <form method="post">
                <?= csrfField() ?>
                <div class="mb-3">
                    <label for="admin-email" class="form-label">Email address</label>
                    <input id="admin-email" name="email" type="email" class="form-control" placeholder="admin@example.com"
                           autocomplete="username" maxlength="254" value="<?= e($email) ?>" required>
                </div>
                <div class="mb-4">
                    <label for="admin-password" class="form-label">Password</label>
                    <input id="admin-password" name="password" type="password" class="form-control" placeholder="Enter your password"
                           autocomplete="current-password" maxlength="72" required>
                </div>
                <button class="btn btn-primary w-100" type="submit">Sign in <?= icon('arrow', 17) ?></button>
            </form>
        </div>
    </section>
</main>
<?php pageEnd(); ?>
