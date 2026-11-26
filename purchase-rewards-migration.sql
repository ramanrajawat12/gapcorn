CREATE TABLE IF NOT EXISTS purchase_reward_settings (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    points INT NOT NULL CHECK (points >= 0),
    effective_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3)
) ENGINE=InnoDB;

INSERT INTO purchase_reward_settings (id, points, effective_at)
VALUES (1, 0, UTC_TIMESTAMP(3))
ON DUPLICATE KEY UPDATE id = id;
