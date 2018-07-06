alter table `jegy` modify `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra') COLLATE utf8_hungarian_ci DEFAULT NULL;
