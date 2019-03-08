alter table tankorTipus add jelleg ENUM('elmélet','gyakorlat') DEFAULT 'elmélet';
INSERT INTO tankorTipus (oratervi,rovidNev,leiras,jelenlet,regisztralando,hianyzasBeleszamit,jelleg) VALUES ('óratervi','gyakorlat','Óratervi (képzési hálóban kötelező) gyakorlat','kötelező','igen','igen','gyakorlat');
