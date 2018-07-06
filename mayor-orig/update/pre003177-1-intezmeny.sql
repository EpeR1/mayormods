ALTER TABLE tankorTipus MODIFY jelleg ENUM('elmélet','gyakorlat','osztályfüggetlen') COLLATE utf8_hungarian_ci DEFAULT 'elmélet';
INSERT INTO tankorTipus (rovidNev,leiras,jelenlet,regisztralando,hianyzasBeleszamit,jelleg)
VALUES ('könyvtár','Könyvtári osztályfüggetlen elfoglaltság (nyitva tartás)','nem kötelező','nem','nem','osztályfüggetlen');