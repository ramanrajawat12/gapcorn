<h1 class="h4 mb-3">Orders</h1>
<?php if ($editProduct): ?>
    <?php require __DIR__ . '/admin-product-edit-view.php'; ?>
<?php elseif ($productEditError !== ''): ?>
    <p class="alert alert-warning" role="alert"><?= e($productEditError) ?></p>
<?php endif; ?>

<section class="table-panel" id="products" aria-labelledby="products-heading">
    <div class="panel-heading"><h2 id="products-heading">Products</h2></div>
    <?php if ($products === []): ?>
        <p class="text-center muted p-4 mb-0">No Products Yet.</p>
    <?php else: ?>
        <div class="table-responsive" tabindex="0" role="region" aria-labelledby="products-heading">
            <table class="table dashboard-table mb-0" aria-labelledby="products-heading">
                <thead>
                    <tr>
                        <th scope="col">Product</th><th scope="col">SKU</th><th scope="col">Price</th>
                        <th scope="col">In Stock</th><th scope="col">Buyers</th><th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <?php if ($product['image_filename']): ?>
                                        <img class="rounded border" src="assets/<?= e(rawurlencode($product['image_filename'])) ?>"
                                             alt="" width="40" height="40" loading="lazy">
                                    <?php endif; ?>
                                    <strong><?= e($product['name']) ?></strong>
                                </div>
                            </td>
                            <td class="text-nowrap"><?= e($product['sku']) ?></td>
                            <td class="text-nowrap">₹<?= number_format($product['price_paise'] / 100, 2) ?></td>
                            <td><?= number_format($product['stock']) ?></td>
                            <td><?= number_format($product['purchaser_count']) ?></td>
                            <td><a class="btn btn-sm btn-outline-primary text-nowrap"
                                   aria-label="Edit <?= e($product['name']) ?>"
                                   href="<?= e($ordersPage . '&edit_product=' . $product['id']) ?>#edit-product">Edit Product</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>

<section class="table-panel mt-4" aria-labelledby="current-orders-heading">
    <div class="panel-heading">
        <h2 id="current-orders-heading">Customers Who Ordered</h2>
    </div>
    <?php if ($orders === []): ?>
        <p class="text-center muted p-4 mb-0">No Orders Recorded Yet.</p>
    <?php else: ?>
        <div class="table-responsive" tabindex="0" role="region" aria-labelledby="current-orders-heading">
            <table class="table dashboard-table mb-0" aria-labelledby="current-orders-heading">
                <thead>
                    <tr>
                        <th scope="col">Customer</th><th scope="col">Order</th><th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orders as $order): ?>
                        <?php [$statusLabel, $statusClass] = match ($order['status']) {
                            'delivered' => ['Delivered', 'bg-success'],
                            'shipped' => ['Shipped', 'bg-primary'],
                            'cancelled' => ['Cancelled', 'bg-secondary'],
                            default => ['Processing', 'bg-warning text-dark'],
                        }; ?>
                        <tr>
                            <td>
                                <a class="fw-bold text-break" href="<?= e($ordersPage . '&order=' . $order['id']) ?>#order-details"
                                   aria-label="<?= e('View Order #' . $order['id'] . ' for ' . ($order['customer_name'] ?: $order['email'])) ?>"
                                   <?= $selectedOrder && (int) $selectedOrder['id'] === (int) $order['id'] ? 'aria-current="true"' : '' ?>>
                                    <?= e($order['customer_name'] ?: $order['email']) ?>
                                </a>
                            </td>
                            <td><strong>#<?= e($order['id']) ?></strong><div class="small muted"><?= e($order['product_name']) ?></div></td>
                            <td><span class="badge rounded-pill <?= e($statusClass) ?>"><?= e($statusLabel) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
<?php if ($selectedOrder): ?>
    <?php require __DIR__ . '/admin-order-details-view.php'; ?>
<?php elseif ($orderId !== false): ?>
    <p id="order-details" class="alert alert-warning mt-3" role="status">Order Could Not Be Found.</p>
<?php endif; ?>
