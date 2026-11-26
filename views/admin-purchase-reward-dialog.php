<div class="modal fade" id="purchase-reward-editor" tabindex="-1" aria-labelledby="purchase-reward-editor-title"
     <?= $error !== '' ? 'data-show-on-load' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update-purchase-reward">
                <input type="hidden" name="default_effective_at" value="<?= e($effectiveDefault) ?>">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="purchase-reward-editor-title">Update purchase reward</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3 mb-3">
                        <strong>Current reward: <?= number_format($current['points']) ?> points per purchase</strong>
                        <div class="row g-2 mt-1 muted small">
                            <div class="col-6">Date effective
                                <time class="d-block" datetime="<?= e(utcTimestamp($current['effective_at'])) ?>"><?= e(displayTime($current['effective_at'], 'd M Y')) ?></time>
                            </div>
                            <div class="col-6">Time effective (IST)
                                <time class="d-block" datetime="<?= e(utcTimestamp($current['effective_at'])) ?>"><?= e(displayTime($current['effective_at'], 'g:i:s A')) ?></time>
                            </div>
                        </div>
                    </div>
                    <?php if ($error !== ''): ?><p class="alert alert-danger" role="alert"><?= e($error) ?></p><?php endif; ?>
                    <label class="form-label" for="purchase-reward-points">Points per completed purchase</label>
                    <input class="form-control" id="purchase-reward-points" name="points" type="number" min="0"
                           max="<?= MAX_POINTS ?>" step="1" value="<?= e($error !== '' ? $amount : $current['points']) ?>"
                           inputmode="numeric" data-modal-focus required>
                    <div class="row g-2 mt-2">
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="purchase-reward-effective-date">Effective date</label>
                            <input class="form-control" id="purchase-reward-effective-date" name="effective_date" type="date"
                                   value="<?= e($effectiveDate) ?>" aria-describedby="purchase-reward-effective-hint" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="purchase-reward-effective-time">Effective time (IST)</label>
                            <input class="form-control" id="purchase-reward-effective-time" name="effective_time" type="time"
                                   value="<?= e($effectiveTime) ?>" aria-describedby="purchase-reward-effective-hint" required>
                        </div>
                    </div>
                    <div class="form-text" id="purchase-reward-effective-hint">Keep the current time to apply now, or choose a future time. Customers receive this amount after a completed product purchase.</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save reward</button>
                </div>
            </form>
        </div>
    </div>
</div>
