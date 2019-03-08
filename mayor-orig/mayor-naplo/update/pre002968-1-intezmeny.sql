alter table `targy` modify `evkoziKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra') collate utf8_hungarian_ci DEFAULT 'jegy';
alter table `targy` modify `zaroKovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra') COLLATE utf8_hungarian_ci DEFAULT 'jegy';
alter table `zaroJegy` modify `jegyTipus` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'százalékos','aláírás','háromszintű','egyedi felsorolás','nem értékelhető','teljesített óra') COLLATE utf8_hungarian_ci NOT NULL DEFAULT 'jegy';
alter table `tankor` modify `kovetelmeny` enum('jegy','magatartás','szorgalom','négyszintű (szöveges minősítés)',
'féljegy','százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL;
alter table `kepzesOraterv` modify `kovetelmeny` enum('jegy','négyszintű (szöveges minősítés)',
'százalékos','aláírás','háromszintű','egyedi felsorolás','szöveges szempontrendszer','teljesített óra','nincs') COLLATE utf8_hungarian_ci DEFAULT NULL;
alter table `targy` MODIFY `targyJelleg` enum('nyelv','szakmai','magatartás','szorgalom','alsó tagozatos','osztályfőnöki','készség','közösségi szolgálat') COLLATE utf8_hungarian_ci DEFAULT NULL;
INSERT INTO tankorTipus (oratervi,rovidNev,leiras,jelenlet,regisztralando, hianyzasBeleszamit,jelleg) VALUES ('tanórán kívüli','közösségi szolgálat','Közösségi szolgálat',
'nem kötelező','igen','nem','gyakorlat');
