<section class="reward-overview" aria-labelledby="dashboard-title">
    <div class="overview-heading"><h1 id="dashboard-title">Operating Dashboard</h1></div>
    <a class="metric text-decoration-none" href="<?= e($customerPage) ?>">
        <span class="metric-icon blue"><?= icon('customers') ?></span>
        <span><span class="metric-label">Registered Customers</span><strong><?= number_format($registeredCount) ?></strong></span>
    </a>
</section>
