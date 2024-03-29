/*
Actualització de la DB a partir de la versió 1.9
*/

ALTER TABLE MODUL_PLA_ESTUDI ADD planificacio TEXT;

ALTER TABLE MATERIAL ADD familia_fp_id INT;
ALTER TABLE MATERIAL ADD
	CONSTRAINT M_FamiliaFPFK FOREIGN KEY (familia_fp_id) REFERENCES FAMILIA_FP(familia_fp_id);	
ALTER TABLE MATERIAL ADD responsable_id INT NOT NULL;
ALTER TABLE MATERIAL ADD
	CONSTRAINT M_UsuariFK FOREIGN KEY (responsable_id) REFERENCES USUARI(usuari_id);	
ALTER TABLE MATERIAL ADD ambit VARCHAR(50);
ALTER TABLE MATERIAL ADD ubicacio VARCHAR(100);

