ALTER TABLE kepzes ADD kepzesEles TINYINT(1) UNSIGNED NOT NULL DEFAULT 1;
UPDATE kepzes SET kepzesEles=1;