-- SQL utasítások a intezmeny adatbázisban --

ALTER TABLE terem MODIFY tipus SET(
    'tanterem','szaktanterem','osztályterem','labor','gépterem','tornaterem','tornaszoba','fejlesztőszoba',
    'tanműhely','előadó','könyvtár','díszterem','tanári','templom','egyéb') NULL;
ALTER TABLE terem MODIFY teremId SMALLINT(5) UNSIGNED NOT NULL;

