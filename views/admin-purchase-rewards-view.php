<?php if (isset($_SESSION['campaign_error'])): ?>
    <p class="alert alert-danger mt-3" role="alert"><?= e($_SESSION['campaign_error']) ?></p>
    <?php unset($_SESSION['campaign_error']); ?>
<?php endif; ?>

<section class="table-panel" aria-labelledby="purchase-reward-heading">
    <div class="panel-heading panel-heading-row flex-wrap gap-2">
        <h1 id="purchase-reward-heading" class="h5 mb-0">Purchase Campaigns</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#purchase-campaign-editor">
            <?= icon('edit', 17) ?> Create Campaign
        </button>
    </div>
    <div class="table-responsive">
    <table class="table dashboard-table mb-0" aria-labelledby="purchase-reward-heading">
        <thead><tr><th scope="col">Campaign</th><th scope="col">Reward Points</th><th scope="col">Start Date</th><th scope="col">Start Time (IST)</th><th scope="col">Expiry Date</th><th scope="col">Expiry Time (IST)</th><th scope="col">Status</th><th scope="col">Actions</th></tr></thead>
        <tbody>
            <?php if ($campaigns === []): ?>
                <tr><td colspan="8" class="text-center muted py-5">No Purchase Reward Campaigns Yet.</td></tr>
            <?php endif; ?>
            <?php foreach ($campaigns as $campaign): ?>
                <?php
                $startsOn = new DateTimeImmutable($campaign['starts_on'], new DateTimeZone('Asia/Kolkata'));
                $expiresOn = new DateTimeImmutable($campaign['expires_on'] . ' ' . $campaign['expires_time'], $campaignNow->getTimezone());
                $expired = $campaignNow >= $expiresOn;
                $enabled = (bool) $campaign['is_enabled'];
                $status = match (true) {
                    $expired => 'Expired',
                    !$enabled => 'Inactive',
                    $campaignNow < $startsOn => 'Scheduled',
                    default => 'Active',
                };
                $statusClass = $status === 'Active' ? 'bg-success' : 'bg-secondary';
                ?>
                <tr>
                    <td><strong><?= e($campaign['name']) ?></strong></td>
                    <td><span class="point-pill"><?= number_format($campaign['points']) ?> Points</span></td>
                    <td><time datetime="<?= e($campaign['starts_on']) ?>"><?= e($startsOn->format('d M Y')) ?></time></td>
                    <td class="text-nowrap"><time datetime="<?= e(utcTimestamp($campaign['created_at'])) ?>"><?= e(displayTime($campaign['created_at'], 'g:i:s A')) ?></time></td>
                    <td><time datetime="<?= e($campaign['expires_on']) ?>"><?= e($expiresOn->format('d M Y')) ?></time></td>
                    <td class="text-nowrap"><?= e($expiresOn->format('g:i:s A')) ?></td>
                    <td><span class="badge rounded-pill <?= $statusClass ?>"><?= $status ?></span></td>
                    <td>
                        <details>
                            <summary class="btn btn-sm btn-outline-primary rounded-circle d-inline-flex align-items-center justify-content-center p-0"
                                     style="width: 1.5rem; height: 1.5rem;" aria-label="Campaign Actions" title="Campaign Actions">
                                <span class="fw-bold lh-1" aria-hidden="true">i</span>
                            </summary>
                            <div class="border rounded bg-white p-2 mt-2">
                                <?php if (!$expired): ?>
                                    <form method="post" action="<?= e($purchaseRewardPage) ?>">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="set-campaign-status">
                                        <input type="hidden" name="campaign_id" value="<?= e($campaign['id']) ?>">
                                        <button class="btn btn-sm <?= $enabled ? 'btn-outline-secondary' : 'btn-outline-primary' ?>"
                                                name="enabled" value="<?= $enabled ? '0' : '1' ?>"><?= $enabled ? 'Deactivate' : 'Activate' ?></button>
                                    </form>
                                <?php endif; ?>
                                <details class="mt-2">
                                    <summary class="text-primary text-nowrap">Extend Expiry</summary>
                                    <form method="post" action="<?= e($purchaseRewardPage) ?>" class="mt-2">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="extend-campaign-expiry">
                                        <input type="hidden" name="campaign_id" value="<?= e($campaign['id']) ?>">
                                        <label class="d-block mb-2">New Expiry Date
                                            <input class="form-control form-control-sm" type="date" name="expires_on"
                                                   min="<?= e(max($campaign['expires_on'], $campaignNow->format('Y-m-d'))) ?>" required>
                                        </label>
                                        <label class="d-block mb-2">New Expiry Time (IST)
                                            <input class="form-control form-control-sm" type="time" name="expires_time" required>
                                        </label>
                                        <button class="btn btn-sm btn-primary text-nowrap" type="submit">Save New Expiry</button>
                                    </form>
                                </details>
                            </div>
                        </details>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</section>
