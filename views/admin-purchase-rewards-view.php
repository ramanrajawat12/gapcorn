<section class="reward-overview" aria-labelledby="purchase-rewards-heading">
    <div class="overview-heading mb-0">
        <div>
            <h1 id="purchase-rewards-heading">Purchase Reward</h1>
            <p class="muted mb-0">Create time-limited reward campaigns for completed product purchases.</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchase-campaign-editor">
            <?= icon('edit', 17) ?> Create campaign
        </button>
    </div>
</section>

<section class="table-panel mt-4" aria-labelledby="purchase-reward-heading">
    <div class="panel-heading panel-heading-row">
        <div>
            <p class="section-label overview-label">Reward Management</p>
            <h2 id="purchase-reward-heading">Purchase campaigns</h2>
        </div>
        <span class="point-pill"><?= number_format(count($campaigns)) ?> campaigns</span>
    </div>
    <div class="table-responsive">
    <table class="table dashboard-table mb-0" aria-labelledby="purchase-reward-heading">
        <thead><tr><th scope="col">Campaign</th><th scope="col">Reward points</th><th scope="col">Start date</th><th scope="col">Expiry date</th><th scope="col">Status</th></tr></thead>
        <tbody>
            <?php if ($campaigns === []): ?>
                <tr><td colspan="5" class="text-center muted py-5">No purchase reward campaigns yet.</td></tr>
            <?php endif; ?>
            <?php $today = new DateTimeImmutable('today', new DateTimeZone('Asia/Kolkata')); ?>
            <?php foreach ($campaigns as $campaign): ?>
                <?php
                $startsOn = new DateTimeImmutable($campaign['starts_on'], new DateTimeZone('Asia/Kolkata'));
                $expiresOn = new DateTimeImmutable($campaign['expires_on'], new DateTimeZone('Asia/Kolkata'));
                $status = $today < $startsOn ? 'Scheduled' : ($today > $expiresOn ? 'Expired' : 'Active');
                $statusClass = $status === 'Active' ? 'bg-success' : ($status === 'Expired' ? 'bg-secondary' : 'bg-warning text-dark');
                ?>
                <tr>
                    <td><strong><?= e($campaign['name']) ?></strong></td>
                    <td><span class="point-pill"><?= number_format($campaign['points']) ?> points</span></td>
                    <td><time datetime="<?= e($campaign['starts_on']) ?>"><?= e($startsOn->format('d M Y')) ?></time></td>
                    <td><time datetime="<?= e($campaign['expires_on']) ?>"><?= e($expiresOn->format('d M Y')) ?></time></td>
                    <td><span class="badge rounded-pill <?= $statusClass ?>"><?= $status ?></span></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</section>
