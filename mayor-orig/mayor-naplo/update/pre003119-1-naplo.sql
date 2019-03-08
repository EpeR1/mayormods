ALTER TABLE ora ADD munkaido ENUM('lekötött','fennmaradó','kötetlen') DEFAULT 'lekötött';
UPDATE ora SET munkaido='lekötött';
UPDATE ora SET munkaido='fennmaradó' WHERE tipus='egyéb';
