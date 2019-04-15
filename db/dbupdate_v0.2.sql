/*
Actualització de la DB a partir de la versió 0.2
*/

CREATE UNIQUE INDEX U_NifIX ON USUARI (document);

ALTER TABLE BLOC_GUARDIA ADD
	professor_lavabo_id INT NULL;
ALTER TABLE BLOC_GUARDIA ADD
	CONSTRAINT BG_ProfessorLavaboFK FOREIGN KEY (professor_lavabo_id) REFERENCES USUARI(usuari_id);	

ALTER TABLE USUARI ADD
	nom_complet VARCHAR(255);
