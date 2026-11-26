<section class="reward-overview" aria-labelledby="reward-heading">
    <div class="overview-heading mb-0">
        <div>
            <h1 id="reward-heading">Welcome Reward</h1>
            <p class="muted mb-0"><?= number_format($current['points']) ?> points per first sign-in</p>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#reward-editor">
            <?= icon('edit', 17) ?> Update reward
        </button>
    </div>
</section>
<section class="table-panel mt-4" aria-labelledby="welcome-reward-heading">
    <div class="panel-heading panel-heading-row">
        <div>
            <p class="section-label overview-label">Reward Management</p>
            <h2 id="welcome-reward-heading">Welcome Reward</h2>
        </div>
        <span class="point-pill"><?= number_format($current['points']) ?> points now</span>
    </div>
    <table class="table dashboard-table mb-0" aria-labelledby="welcome-reward-heading">
        <thead><tr><th scope="col">Reward</th><th scope="col">Effective date</th><th scope="col">Effective time (IST)</th></tr></thead>
        <tbody>
            <?php foreach ($settings as $setting): ?>
                <tr>
                    <td><?= number_format($setting['points']) ?> points
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
