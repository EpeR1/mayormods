-- A tanév adatbázis módosítása

ALTER TABLE hianyzas MODIFY igazolas ENUM('orvosi','szülői','osztályfőnöki','tanulmányi verseny','nyelvvizsga','igazgatói','hatósági','') NULL;