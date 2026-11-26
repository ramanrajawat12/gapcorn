<?php
$now = time();
?>
<section class="reward-overview directory-overview" aria-labelledby="directory-title">
    <div class="overview-heading">
        <div>
            <p class="section-label overview-label">Customer directory</p>
            <h1 id="directory-title">Customers</h1>
            <p class="muted mb-0">View registered customer profiles and purchase details.</p>
        </div>
    </div>
</section>
<section class="table-panel directory-panel mt-4" aria-labelledby="directory-heading">
    <div class="panel-heading panel-heading-row directory-panel-heading">
        <div><p class="section-label overview-label">Directory</p><h2 id="directory-heading">All customers</h2></div>
        <div class="directory-search">
            <?= icon('search', 17) ?>
            <label for="customer-search" class="visually-hidden">Search customers</label>
            <input class="form-control" id="customer-search" type="search" placeholder="Search customers">
        </div>
    </div>
    <?php if ($error !== ''): ?><p class="alert alert-danger m-3" role="alert"><?= e($error) ?></p><?php endif; ?>
    <div class="directory-scroll" id="customer-table-scroll" tabindex="0" role="region" aria-label="Customer table columns">
        <table class="table dashboard-table directory-table mb-0" aria-labelledby="directory-heading" role="table">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">Customer</th>
                    <th scope="col" role="columnheader" title="Active in the last 24 hours">Status</th>
                    <th scope="col" role="columnheader">Last active</th>
                    <th scope="col" role="columnheader">Joined</th>
                    <th scope="col" role="columnheader">Points</th>
                    <th scope="col" role="columnheader">Actions</th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                <?php foreach ($customers as $customer): ?>
                    <?php
                    $name = trim((string) $customer['name']) !== '' ? $customer['name'] : $customer['email'];
                    $initials = strtoupper(substr($name, 0, 1));
                    $joined = (new DateTimeImmutable($customer['joined_at']))
                        ->setTimezone(new DateTimeZone('Asia/Kolkata'))->format('M j, Y');
                    $lastActive = $customer['last_active_at'];
                    $elapsed = max(0, $now - (new DateTimeImmutable($lastActive))->getTimestamp());
                    $active = $elapsed < 86400;
                    $lastActiveLabel = match (true) {
                        $elapsed < 60 => 'Just now',
                        $elapsed < 120 => '1 min ago',
                        $elapsed < 3600 => intdiv($elapsed, 60) . ' min ago',
                        $elapsed < 7200 => '1 hour ago',
                        $elapsed < 86400 => intdiv($elapsed, 3600) . ' hours ago',
                        $elapsed < 172800 => '1 day ago',
                        default => intdiv($elapsed, 86400) . ' days ago',
                    };
                    ?>
                    <tr role="row" data-directory-row>
                        <td class="directory-person" role="cell">
                            <div class="profile">
                                <span class="profile-avatar" aria-hidden="true"><?= e($initials) ?></span>
                                <div class="profile-copy">
                                    <strong><?= e($name) ?></strong>
                                </div>
                            </div>
                        </td>
                        <td class="directory-status" role="cell">
                            <span class="customer-status <?= $active ? 'is-active' : 'is-inactive' ?>"><?= $active ? 'Active' : 'Inactive' ?></span>
                        </td>
                        <td class="directory-activity" role="cell">
                            <time datetime="<?= e(utcTimestamp($lastActive)) ?>" title="<?= e(displayTime($lastActive)) ?> IST"><?= e($lastActiveLabel) ?></time>
                        </td>
                        <td class="directory-joined" role="cell">
                            <time datetime="<?= e(utcTimestamp($customer['joined_at'])) ?>" title="<?= e(displayTime($customer['joined_at'])) ?> IST"><?= e($joined) ?></time>
                        </td>
                        <td class="directory-points" role="cell"><span class="point-pill"><?= number_format($customer['points']) ?></span></td>
                        <td class="directory-action" role="cell">
                            <button class="btn btn-outline-primary view-customer" type="button" data-bs-toggle="modal" data-bs-target="#customer-details"
                                    data-name="<?= e($name) ?>" data-email="<?= e($customer['email']) ?>"
                                    data-phone="<?= e($customer['phone'] ?? '') ?>" data-address="<?= e($customer['address'] ?? '') ?>"
                                    data-products="<?= e($customer['products_purchased']) ?>" data-points="<?= e($customer['points']) ?>"
                                    aria-label="View details for <?= e($name) ?>" title="View customer details"><?= icon('eye', 18) ?></button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <input class="directory-slider" id="customer-table-slider" type="range" min="0" max="1" value="0"
           aria-label="Scroll customer table horizontally" aria-controls="customer-table-scroll" hidden>
    <p class="empty-state mb-0" id="customer-no-results" role="status" hidden><?= $customers === [] ? 'No customers have registered yet.' : 'No customers match your search.' ?></p>
</section>
<div class="modal fade" id="customer-details" tabindex="-1" aria-labelledby="customer-details-title">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <p class="section-label overview-label mb-1">Customer profile</p>
                    <h2 class="modal-title fs-5" id="customer-details-title">Customer details</h2>
                </div>
                <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body customer-detail-grid">
                <div class="customer-detail"><span>Name</span><strong id="detail-customer-name"></strong></div>
                <div class="customer-detail"><span>Email address</span><strong id="detail-customer-email" class="text-break"></strong></div>
                <div class="customer-detail"><span>Phone number</span><strong id="detail-customer-phone"></strong></div>
                <div class="customer-detail"><span>Points</span><strong id="detail-customer-points"></strong></div>
                <div class="customer-detail customer-detail-wide"><span>Address</span><strong id="detail-customer-address"></strong></div>
                <div class="customer-detail customer-detail-wide"><span>Products purchased</span><strong id="detail-customer-products"></strong></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary" type="button" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
