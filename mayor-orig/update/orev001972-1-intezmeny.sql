ALTER TABLE `tankor` MODIFY `tankorTipus` enum('tanórai','tanórán kívüli','első nyelv','második nyelv','egyéni foglalkozás','délutáni') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'tanórai';
UPDATE `tankor` SET `tankorTipus`='tanórai' WHERE `tankorTipus` IS NULL OR `tankorTipus`='';
