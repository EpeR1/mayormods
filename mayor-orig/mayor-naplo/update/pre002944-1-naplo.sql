-- jegy
alter table `jegy` modify `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','három szintű','háromszintű','egyedi felsorolás','szöveges','szöveges szempontrendszer')
COLLATE utf8_hungarian_ci DEFAULT NULL;
update `jegy` set `jegyTipus` = 'szöveges szempontrendszer' where `jegyTipus` = 'szöveges';
update `jegy` set `jegyTipus` = 'háromszintű' where `jegyTipus` = 'három szintű';
alter table `jegy` modify `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer') COLLATE utf8_hungarian_ci DEFAULT NULL;
