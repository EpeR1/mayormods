alter table zaroJegy MODIFY jegy decimal(4,1) NOT NULL;
update targy SET evkoziKovetelmeny='magatartás',zaroKovetelmeny='magatartás',targyJelleg='magatartás' WHERE targyNev='magatartás';
update targy SET evkoziKovetelmeny='szorgalom',zaroKovetelmeny='szorgalom',targyJelleg='szorgalom'  WHERE targyNev='szorgalom';