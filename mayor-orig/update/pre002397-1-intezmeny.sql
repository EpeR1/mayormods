
alter table idoszak modify
`tipus` enum('zárás','bizonyítvány írás','vizsga','előzetes tárgyválasztás','tárgyválasztás','tankörnévsor módosítás','fogadóóra jelentkezés') 
COLLATE utf8_hungarian_ci DEFAULT NULL;