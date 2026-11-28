ALTER TABLE purchase_reward_campaigns
    ADD COLUMN expires_time TIME NOT NULL DEFAULT '23:59:59',
    ADD COLUMN is_enabled TINYINT(1) NOT NULL DEFAULT 1;
