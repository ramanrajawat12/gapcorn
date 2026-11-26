USE gapcorn;

CREATE TABLE IF NOT EXISTS purchase_reward_campaigns (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    points INT NOT NULL CHECK (points >= 0),
    starts_on DATE NOT NULL,
    expires_on DATE NOT NULL,
    created_at DATETIME(3) NOT NULL DEFAULT CURRENT_TIMESTAMP(3),
    CHECK (expires_on >= starts_on)
) ENGINE=InnoDB;
