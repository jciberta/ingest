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
ALTER TABLE USUARI ADD
	permet_tutor BIT NOT NULL DEFAULT 0;

/*
 * Edat
 *
 * Calcula l'edat donada una data de naixement.
 *
 * Ús:
 *   SELECT nom, cognom1, data_naixement, Edat(data_naixement) AS edat FROM USUARI;
 *
 * @param datetime data_naixement Data per calcular l'edat.
 * @return integer Anys entre la data de naixement i ara.
 */
DELIMITER //
CREATE FUNCTION Edat(data_naixement DATETIME)
RETURNS INT
BEGIN
    RETURN TIMESTAMPDIFF (YEAR, data_naixement, CURDATE());
END //
DELIMITER ;
