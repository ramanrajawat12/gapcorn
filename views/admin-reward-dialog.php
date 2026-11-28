<div class="modal fade" id="reward-editor" tabindex="-1" aria-labelledby="reward-editor-title"
     <?= $error !== '' ? 'data-show-on-load' : '' ?>>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="post">
                <?= csrfField() ?>
                <input type="hidden" name="action" value="update-reward">
                <input type="hidden" name="default_effective_at" value="<?= e($effectiveDefault) ?>">
                <input type="hidden" name="effective_date" value="<?= e($effectiveDate) ?>">
                <input type="hidden" name="effective_time" value="<?= e($effectiveTime) ?>">
                <div class="modal-header">
                    <h2 class="modal-title fs-5" id="reward-editor-title">Update First Sign-In Reward</h2>
                    <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="border rounded p-3 mb-3">
                        <strong>Current Reward: <?= number_format($current['points']) ?> Points</strong>
                        <div class="row g-2 mt-1 muted small">
                            <div class="col-6">Date Effective
                                <time class="d-block" datetime="<?= e(utcTimestamp($current['effective_at'])) ?>"><?= e(displayTime($current['effective_at'], 'd M Y')) ?></time>
                            </div>
                            <div class="col-6">Time Effective (IST)
                                <time class="d-block" datetime="<?= e(utcTimestamp($current['effective_at'])) ?>"><?= e(displayTime($current['effective_at'], 'g:i:s A')) ?></time>
                            </div>
                        </div>
                    </div>
                    <?php if ($error !== ''): ?><p class="alert alert-danger" role="alert"><?= e($error) ?></p><?php endif; ?>
                    <label class="form-label" for="reward-points">Reward Points</label>
                    <input class="form-control" id="reward-points" name="points" type="number" min="0"
                           max="<?= MAX_POINTS ?>" step="1" value="<?= e($error !== '' ? $amount : $current['points']) ?>"
                           inputmode="numeric" data-modal-focus required>
                    <div class="row g-2 mt-2">
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="reward-effective-date">Effective Date</label>
                            <input class="form-control" id="reward-effective-date" type="date"
                                   value="<?= e($effectiveDate) ?>" aria-describedby="reward-effective-hint" disabled>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label" for="reward-effective-time">Effective Time (IST)</label>
                            <input class="form-control" id="reward-effective-time" type="time"
                                   value="<?= e($effectiveTime) ?>" aria-describedby="reward-effective-hint" disabled>
                        </div>
                    </div>
                    <div class="form-text" id="reward-effective-hint">The Effective Date and Time Are Selected Automatically When This Form Opens. The New Amount Applies Only to Customers Who Join from Then on.</div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
                    <button class="btn btn-primary" type="submit">Save Reward</button>
                </div>
            </form>
        </div>
    </div>
</div>
