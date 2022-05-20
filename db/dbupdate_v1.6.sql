/*
Actualització de la DB a partir de la versió 1.6
*/

ALTER TABLE UNITAT_PLA_ESTUDI ADD metode_importacio_notes CHAR(1) NOT NULL DEFAULT 'F'; /* Fitxer, servei Web */

ALTER TABLE MODUL_PLA_ESTUDI ADD estat CHAR(1) NOT NULL DEFAULT 'E'; /* Elaboració, Departament, esTudis, Acceptada */

ALTER TABLE EQUIP ADD familia_fp_id INT;
ALTER TABLE EQUIP ADD
	CONSTRAINT EQP_FamiliaFPFK FOREIGN KEY (familia_fp_id) REFERENCES FAMILIA_FP(familia_fp_id);	
