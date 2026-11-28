<section class="table-panel" aria-labelledby="welcome-reward-heading">
    <div class="panel-heading panel-heading-row flex-wrap gap-2">
        <h1 id="welcome-reward-heading" class="h5 mb-0">Reward History</h1>
        <button type="button" class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#reward-editor">
            <?= icon('edit', 17) ?> Update Reward
        </button>
    </div>
    <table class="table dashboard-table mb-0" aria-labelledby="welcome-reward-heading">
        <thead><tr><th scope="col">Reward</th><th scope="col">Effective Date</th><th scope="col">Effective Time (IST)</th></tr></thead>
        <tbody>
            <?php foreach ($settings as $setting): ?>
                <tr>
                    <td><?= number_format($setting['points']) ?> Points
                        <?php if ($setting['id'] === $current['id']): ?>
                            <span class="current-label">Current</span>
                        <?php elseif ($setting['effective_at'] > $current['effective_at']): ?>
                            <span class="badge rounded-pill bg-warning text-dark">Scheduled</span>
                        <?php endif; ?>
                    </td>
                    <td><time datetime="<?= e(utcTimestamp($setting['effective_at'])) ?>"><?= e(displayTime($setting['effective_at'], 'd M Y')) ?></time></td>
                    <td><time datetime="<?= e(utcTimestamp($setting['effective_at'])) ?>"><?= e(displayTime($setting['effective_at'], 'g:i:s A')) ?></time></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</section>
