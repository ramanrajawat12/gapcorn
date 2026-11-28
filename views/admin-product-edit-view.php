<section class="table-panel mb-4" id="edit-product" aria-labelledby="edit-product-heading" tabindex="-1">
    <div class="panel-heading"><h2 id="edit-product-heading">Edit Product</h2></div>
    <form method="post" action="<?= e($ordersPage) ?>" class="p-3 p-md-4">
        <?= csrfField() ?>
        <input type="hidden" name="action" value="update-product">
        <input type="hidden" name="product_id" value="<?= e($editProduct['id']) ?>">
        <?php if ($productEditError !== ''): ?>
            <p class="alert alert-danger" role="alert"><?= e($productEditError) ?></p>
        <?php endif; ?>
        <div class="row g-3">
            <div class="col-md-8">
                <label for="product-name" class="form-label">Product Name</label>
                <input id="product-name" class="form-control" name="name" maxlength="180" value="<?= e($editProduct['name']) ?>" required>
            </div>
            <div class="col-md-4">
                <label for="product-sku" class="form-label">SKU</label>
                <input id="product-sku" class="form-control" name="sku" maxlength="48" value="<?= e($editProduct['sku']) ?>" required>
            </div>
            <div class="col-sm-6">
                <label for="product-price" class="form-label">Price (₹)</label>
                <input id="product-price" class="form-control" name="price" inputmode="decimal" maxlength="11" value="<?= e($editProduct['price']) ?>" required>
            </div>
            <div class="col-sm-6">
                <label for="product-stock" class="form-label">In Stock</label>
                <input id="product-stock" class="form-control" type="number" name="stock" min="0" max="4294967295" step="1" value="<?= e($editProduct['stock']) ?>" required>
            </div>
            <div class="col-12">
                <label for="product-description" class="form-label">Description</label>
                <textarea id="product-description" class="form-control" name="description" maxlength="2000" rows="3"><?= e($editProduct['description']) ?></textarea>
            </div>
        </div>
        <div class="d-flex flex-wrap gap-2 mt-3">
            <button class="btn btn-primary" type="submit">Save Product</button>
            <a class="btn btn-outline-secondary" href="<?= e($ordersPage) ?>#products">Cancel</a>
        </div>
    </form>
</section>
