-- targy
-- három szintű --> háromszintű
-- szöveges --> szöveges szempontrendszer
-- + négyszintű (szöveges minősítés)
alter table `targy` modify `evkoziKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','három szintű','háromszintű','egyedi felsorolás','szöveges','szöveges szempontrendszer') collate utf8_hungarian_ci DEFAULT 'jegy';
alter table `targy` modify `zaroKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','három szintű','háromszintű','egyedi felsorolás','szöveges','szöveges szempontrendszer') COLLATE utf8_hungarian_ci DEFAULT 'jegy';
update `targy` set `evkoziKovetelmeny` = 'szöveges szempontrendszer' where `evkoziKovetelmeny` = 'szöveges';
update `targy` set `evkoziKovetelmeny` = 'háromszintű' where `evkoziKovetelmeny` = 'három szintű';
update `targy` set `zaroKovetelmeny` = 'szöveges szempontrendszer' where `zaroKovetelmeny` = 'szöveges';
update `targy` set `zaroKovetelmeny` = 'háromszintű' where `zaroKovetelmeny` = 'három szintű';
alter table `targy` modify `evkoziKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer') collate utf8_hungarian_ci DEFAULT 'jegy';
alter table `targy` modify `zaroKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer') COLLATE utf8_hungarian_ci DEFAULT 'jegy';

-- zaroJegy
-- három szintű --> háromszintű
-- felmentett --> nem értékelhető
-- + négyszintű (szöveges minősítés)
alter table `zaroJegy` modify `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'százalékos','aláírás','három szintű','háromszintű','egyedi felsorolás','felmentett','nem értékelhető') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'jegy';
update `zaroJegy` set `jegyTipus` = 'háromszintű' where `jegyTipus` = 'három szintű';
update `zaroJegy` set `jegyTipus` = 'nem értékelhető' where `jegyTipus` = 'felmentett';
alter table `zaroJegy` modify `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'százalékos','aláírás','háromszintű','egyedi felsorolás','nem értékelhető') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'jegy';

-- tankor
-- három szintű --> háromszintű
-- szöveges --> szöveges szempontrendszer
-- + négyszintű (szöveges minősítés)
alter table `tankor` modify `kovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','három szintű','háromszintű','egyedi felsorolás','szöveges','szöveges szempontrendszer',
'nincs') COLLATE utf8_hungarian_ci DEFAULT NULL;
update `tankor` set `kovetelmeny` = 'szöveges szempontrendszer' where `kovetelmeny` = 'szöveges';
update `tankor` set `kovetelmeny` = 'háromszintű' where `kovetelmeny` = 'három szintű';
alter table `tankor` modify `kovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL;


-- kepzesOraterv
-- három szintű --> háromszintű
-- szöveges --> szöveges szempontrendszer
-- + négyszintű (szöveges minősítés)
alter table `kepzesOraterv` modify `kovetelmeny` enum('jegy','négyszintű (szöveges minősítés)',
'százalékos','aláírás','három szintű','háromszintű','egyedi felsorolás','szöveges','szöveges szempontrendszer','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL;
update `kepzesOraterv` set `kovetelmeny` = 'szöveges szempontrendszer' where `kovetelmeny` = 'szöveges';
update `kepzesOraterv` set `kovetelmeny` = 'háromszintű' where `kovetelmeny` = 'három szintű';
alter table `kepzesOraterv` modify `kovetelmeny` enum('jegy','négyszintű (szöveges minősítés)',
'százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL;
