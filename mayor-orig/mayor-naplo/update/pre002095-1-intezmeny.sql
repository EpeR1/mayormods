
alter table szemeszter modify `statusz` enum('aktív','lezárt','archivált','tervezett') COLLATE utf8_hungarian_ci DEFAULT 'tervezett';
