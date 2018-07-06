
-- A zárójegy tábla felesleges mezőinek törlése
alter table zaroJegy drop column _tanev;
alter table zaroJegy drop column _szemeszter;