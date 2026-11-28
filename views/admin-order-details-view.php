<?php
$paid = $selectedOrder['paid_at'] !== null;
$cancelled = $selectedOrder['status'] === 'cancelled';
$shipped = in_array($selectedOrder['status'], ['shipped', 'delivered'], true);
$delivered = $selectedOrder['status'] === 'delivered';
$milestones = [
    ['Payment Done', $paid, $paid ? displayTime($selectedOrder['paid_at']) . ' IST' : 'Payment Not Recorded'],
    ['Order Placed', true, displayTime($selectedOrder['ordered_at']) . ' IST'],
    ['Shipped', $shipped, $shipped ? 'Completed' : ($cancelled ? 'Cancelled' : 'Pending')],
    ['Delivered', $delivered, $delivered ? 'Completed' : ($cancelled ? 'Cancelled' : 'Pending')],
];
?>
<section class="table-panel mt-4" id="order-details" aria-labelledby="order-details-heading" tabindex="-1">
    <header class="panel-heading panel-heading-row">
        <h2 id="order-details-heading">Order #<?= e($selectedOrder['id']) ?></h2>
        <a class="btn btn-sm btn-outline-secondary" href="<?= e($ordersPage) ?>#current-orders-heading">Close Details</a>
    </header>
    <div class="row g-4 p-3 p-md-4">
        <div class="col-md-6">
            <h3 class="h6 mb-3">Order Progress</h3>
            <?php if ($cancelled): ?><p class="badge bg-secondary">Order Cancelled</p><?php endif; ?>
            <ol class="order-timeline list-unstyled mb-0">
                <?php foreach ($milestones as [$label, $complete, $status]): ?>
                    <li class="order-timeline-step<?= $complete ? ' is-complete' : '' ?>">
                        <span class="order-timeline-dot" aria-hidden="true"><?= $complete ? icon('check', 14) : '' ?></span>
                        <div><strong><?= e($label) ?></strong><div class="small muted"><?= e($status) ?></div></div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </div>
        <div class="col-md-6">
            <h3 class="h6 mb-3">Customer Details</h3>
            <dl class="mb-3">
                <dt>Name</dt><dd class="text-break"><?= e($selectedOrder['customer_name'] ?: 'Not Provided') ?></dd>
                <dt>Email Address</dt><dd class="text-break"><?= e($selectedOrder['email']) ?></dd>
                <dt>Phone Number</dt><dd class="text-break"><?= e($selectedOrder['phone'] ?: 'Not Provided') ?></dd>
                <dt>Address</dt><dd class="text-break"><?= nl2br(e($selectedOrder['address'] ?: 'Not Provided')) ?></dd>
            </dl>
            <p class="mb-1 fw-bold"><?= e($selectedOrder['product_name']) ?></p>
            <p class="mb-0"><?= number_format($selectedOrder['quantity']) ?> Units · ₹<?= number_format(((int) $selectedOrder['quantity'] * (int) $selectedOrder['unit_price_paise']) / 100, 2) ?></p>
        </div>
    </div>
</section>
