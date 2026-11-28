<?php
declare(strict_types=1);

$id = filter_var(post('product_id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if ($id === false) {
    http_response_code(400);
    exit('Invalid Product.');
}

$name = trim(post('name'));
$sku = strtoupper(trim(post('sku')));
$description = trim(post('description'));
$price = trim(post('price'));
$stockInput = trim(post('stock'));
$stock = filter_var($stockInput, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 4294967295]]);
$pricePaise = null;
if (preg_match('/\A(\d{1,8})(?:\.(\d{1,2}))?\z/', $price, $parts)) {
    $pricePaise = (int) $parts[1] * 100 + (int) str_pad($parts[2] ?? '', 2, '0');
}

if (preg_match('/\A.{1,180}\z/u', $name) !== 1 || preg_match('/\A.{0,2000}\z/us', $description) !== 1) {
    $error = 'Enter a Product Name and Keep the Description Within 2,000 Characters.';
} elseif (!preg_match('/\A[A-Z0-9][A-Z0-9._-]{0,47}\z/', $sku)) {
    $error = 'Use Letters, Numbers, Dots, Hyphens, or Underscores for the SKU.';
} elseif ($pricePaise === null || $pricePaise > 4294967295 || $stock === false) {
    $error = 'Enter a Valid Price With Up to Two Decimal Places and a Whole Stock Count.';
} else {
    try {
        $statement = $db->prepare('UPDATE store_products SET name = ?, sku = ?, description = ?, price_paise = ?, stock = ? WHERE id = ?');
        $statement->execute([$name, $sku, $description, $pricePaise, $stock, $id]);
        if ($statement->rowCount() > 0) {
            $_SESSION['admin_notice'] = 'Product Details Saved.';
            redirect($ordersPage . '#products');
        }
        $error = 'Product Could Not Be Found.';
    } catch (PDOException $exception) {
        if ((int) ($exception->errorInfo[1] ?? 0) !== 1062) {
            throw $exception;
        }
        $error = 'Another Product Already Uses This SKU.';
    }
}

$_SESSION['product_edit_error'] = [
    'id' => $id, 'message' => $error, 'name' => $name, 'sku' => $sku,
    'description' => $description, 'price' => $price, 'stock' => $stockInput,
];
redirect($ordersPage . '&edit_product=' . $id . '#edit-product');
