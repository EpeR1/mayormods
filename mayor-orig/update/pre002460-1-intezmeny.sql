
alter table szovegesErtekeles drop key `sze_UKindex1`;
alter table szovegesErtekeles add unique key `sze_UKindex1` (`diakId`,`targyId`,`tanev`,`szemeszter`);
