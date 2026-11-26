<div class="modal fade" id="purchase-campaign-editor" tabindex="-1" aria-labelledby="purchase-campaign-editor-title"
     <?= $error !== '' ? 'data-show-on-load' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="create-purchase-campaign">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="purchase-campaign-editor-title">Create purchase campaign</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php if ($error !== ''): ?><p class="alert alert-danger" role="alert"><?= e($error) ?></p><?php endif; ?>
                    <div class="mb-3">
                        <label class="form-label" for="campaign-name">Campaign name</label>
                        <input class="form-control" id="campaign-name" name="campaign_name" type="text" minlength="3" maxlength="120"
                               value="<?= e($campaignName) ?>" placeholder="Diwali Season Campaign" data-modal-focus required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="campaign-points">Reward points</label>
                        <input class="form-control" id="campaign-points" name="points" type="number" min="0" max="<?= MAX_POINTS ?>"
                               step="1" value="<?= e($amount) ?>" inputmode="numeric" required>
                        <div class="form-text">These points will be granted after a completed product purchase during the campaign.</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="campaign-start">Start date</label>
                            <input class="form-control" id="campaign-start" name="starts_on" type="date"
                                   value="<?= e($campaignStart) ?>" readonly required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="campaign-expiry">Expiry date</label>
                            <input class="form-control" id="campaign-expiry" name="expires_on" type="date"
                                   min="<?= e($campaignStart) ?>" value="<?= e($campaignExpiry) ?>" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Create campaign</button>
                </div>
            </form>
        </div>
    </div>
</div>
