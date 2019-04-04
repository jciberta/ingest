/*
Actualització de la DB a partir de la versió 0.1
*/

ALTER TABLE CURS ADD
	avaluacio CHAR(3) NOT NULL DEFAULT 'ORD'; /* ORD, EXT */
ALTER TABLE CURS ADD
    trimestre INT NOT NULL DEFAULT 1; /* 1, 2, 3 */
ALTER TABLE CURS ADD
	butlleti_visible BIT NOT NULL DEFAULT 0;
ALTER TABLE CURS ADD
	finalitzat BIT NOT NULL DEFAULT 0;
	
ALTER TABLE USUARI ADD
	nom_complet VARCHAR(255);
