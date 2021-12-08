/*
Actualització de la DB a partir de la versió 1.2
*/

ALTER TABLE MODUL_PLA_ESTUDI ADD
	metodologia TEXT;
ALTER TABLE MODUL_PLA_ESTUDI ADD
	criteris_avaluacio TEXT;
ALTER TABLE MODUL_PLA_ESTUDI ADD
	recursos TEXT;


