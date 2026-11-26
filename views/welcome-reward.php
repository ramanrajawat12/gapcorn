<?php
$formattedReward = number_format($welcomePoints);
?>
<div class="modal fade customer-modal" id="welcome-reward" tabindex="-1" aria-labelledby="welcome-title"
     aria-describedby="welcome-message" data-show-on-load>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="reward-stage">
                <div class="reward-confetti" aria-hidden="true"></div>
                <span class="reward-popper reward-popper-left" aria-hidden="true"></span>
                <span class="reward-popper reward-popper-right" aria-hidden="true"></span>
                <span class="reward-spark reward-spark-left" aria-hidden="true"></span>
                <span class="reward-spark reward-spark-right" aria-hidden="true"></span>
                <h2 id="welcome-title" class="reward-title">
                    <span>You earned</span><span class="reward-ticket-shine" aria-hidden="true"></span>
                    <span class="reward-number-window">
                        <span class="visually-hidden"><?= e($formattedReward) ?></span>
                        <strong class="reward-number<?= $welcomePoints >= 1000 ? ' wide-reward' : '' ?>" aria-hidden="true">
                            <?php foreach (str_split($formattedReward) as $character): ?>
                                <span class="<?= $character === ',' ? 'reward-separator' : 'reward-digit' ?>"><?= e($character) ?></span>
                            <?php endforeach; ?>
                        </strong>
                    </span>
                    <span class="reward-ticket-unit">points! <span class="reward-stamp" aria-hidden="true"><?= icon('check', 14) ?></span></span>
                </h2>
            </div>
            <div class="modal-body reward-actions">
                <p id="welcome-message" class="muted mb-3">Your first sign-in reward is in your points balance.</p>
                <button class="btn btn-customer w-100" type="button" data-bs-dismiss="modal" data-modal-focus>View my points <?= icon('arrow', 17) ?></button>
            </div>
        </div>
    </div>
</div>
