-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (1,'felkészülés foglalkozásra, tanórára',10);
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (2,'diákok teljesítményének értékelése',10);
-- 3. az intézmény kulturális és sportéletének, versenyeknek, a szabadidő hasznos eltöltésének megszervezése,
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (31,'sportélet és szabadidő szervezése',10);
UPDATE feladatTipus SET feladatTipusLeiras='kulturális és sportélet, valamint szabadidő szervezése' WHERE feladatTipusId=31;
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (32,'versenyfelügyelet, versenyeztetés',10);
UPDATE feladatTipus SET feladatTipusLeiras='versenyeztetés, versenyfelkészítés' WHERE feladatTipusId=32;
-- 4. a tanulók nevelési-oktatási intézményen belüli önszerveződésének segítésével összefüggő feladatok végrehajtása,
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (4,'diákönkormányzat segítése',10);
-- 5. ???
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (51,'felügyelet (versenyeken, rendezvényeken)',10);
-- 6. a tanuló- és gyermekbalesetek megelőzésével kapcsolatos feladatok végrehajtása, NEW
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (6,'balesetmegelőzés',10);
-- 
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (7,'gyermek- és ifjúságvédelmi feladat',10);
-- 8-as kimarad, máshol kezeljük
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (9,'adminisztráció, dokumentumkészítés',10);
-- 9-essel együtt
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (11,'szülői értekezlet, fogadóóra (kapcsolattartás)',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (12,'osztályfőnöki feladat',10);
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (13,'mentorálás',10);
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (14,'nevelőtestületi, munkaközösségi feladat',10);
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (16,'intézményfejlesztés, -karbantartás',10);
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (17,'környezeti nevelés',10);
-- 18=16
-- 19=hangszerkarbantartás
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (20,'telephelyközi utazás',10);
-- INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (22,'iskolai redezvény',10);
INSERT INTO feladatTipus (feladatTipusId,feladatTipusLeiras,beszamithatoMaxOra) VALUES (23,'nem rendszeres foglalkozás (korrepetálás, tehetséggondozás)',10);
