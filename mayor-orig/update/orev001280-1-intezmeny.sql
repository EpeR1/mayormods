UPDATE tankorDiak SET jelenlet='kötezelő',kovetelmeny='jegy' WHERE kiDt is NULL AND (jelenlet='' OR kovetelmeny='');
UPDATE tankorDiak SET jelenlet='nem kötezelő',kovetelmeny='aláírás' WHERE kiDt is NOT NULL AND (jelenlet='' OR kovetelmeny='');
alter table tankorDiak modify jelenlet enum('kötelező','nem kötelező') NOT NULL DEFAULT 'kötelező';
alter table tankorDiak modify kovetelmeny enum('aláírás','vizsga','jegy') NOT NULL DEFAULT 'jegy';
