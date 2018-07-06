alter table osztalyTanar drop primary key;
alter table osztalyTanar add primary key(osztalyId, tanarId, beDt);
