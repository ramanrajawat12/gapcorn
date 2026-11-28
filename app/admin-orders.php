<?php
declare(strict_types=1);

$products = $db->query("SELECT p.id, p.sku, p.name, p.price_paise, p.stock, p.image_filename,
    (SELECT COUNT(DISTINCT o.customer_id) FROM store_orders o
     WHERE o.product_id = p.id AND o.status <> 'cancelled') AS purchaser_count
    FROM store_products p ORDER BY p.name, p.id")->fetchAll();
$orders = $db->query('SELECT o.id, o.status, c.name AS customer_name, c.email, p.name AS product_name
    FROM store_orders o
    JOIN customers c ON c.id = o.customer_id
    JOIN store_products p ON p.id = o.product_id
    ORDER BY o.ordered_at DESC, o.id DESC')->fetchAll();

$selectedOrder = null;
$orderId = filter_var($_GET['order'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($orderId !== false) {
    $statement = $db->prepare('SELECT o.id, o.quantity, o.unit_price_paise, o.status, o.ordered_at, o.paid_at,
        c.name AS customer_name, c.email, c.phone, c.address, p.name AS product_name
        FROM store_orders o
        JOIN customers c ON c.id = o.customer_id
        JOIN store_products p ON p.id = o.product_id
        WHERE o.id = ?');
    $statement->execute([$orderId]);
    $selectedOrder = $statement->fetch() ?: null;
}

$editProduct = null;
$productEditError = '';
$editProductId = filter_var($_GET['edit_product'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$attempt = $_SESSION['product_edit_error'] ?? null;
unset($_SESSION['product_edit_error']);
if ($editProductId !== false) {
    $statement = $db->prepare('SELECT id, sku, name, description, price_paise, stock FROM store_products WHERE id = ?');
    $statement->execute([$editProductId]);
    $editProduct = $statement->fetch() ?: null;
    if ($editProduct === null) {
        $productEditError = 'Product Could Not Be Found.';
    } else {
        $editProduct['price'] = number_format($editProduct['price_paise'] / 100, 2, '.', '');
        if (($attempt['id'] ?? null) === $editProductId) {
            $productEditError = $attempt['message'];
            $editProduct = $attempt + $editProduct;
        }
    }
}
