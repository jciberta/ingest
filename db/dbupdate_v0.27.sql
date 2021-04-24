/*
Actualització de la DB a partir de la versió 0.27
*/

ALTER TABLE CURS ADD estat char(1) NOT NULL DEFAULT 'A';
ALTER TABLE CURS DROP COLUMN butlleti_visible;
ALTER TABLE CURS DROP COLUMN finalitzat;

ALTER TABLE USUARI ADD email_ins VARCHAR(100);

ALTER TABLE CICLE_FORMATIU ADD actiu BIT NOT NULL DEFAULT 1;
ALTER TABLE MODUL_PROFESSIONAL ADD actiu BIT NOT NULL DEFAULT 1;
ALTER TABLE UNITAT_FORMATIVA ADD activa BIT NOT NULL DEFAULT 1;

CREATE TABLE CICLE_PLA_ESTUDI
(
    /* CPE */
    cicle_pla_estudi_id INT NOT NULL AUTO_INCREMENT,
    cicle_formatiu_id INT NOT NULL,
    any_academic_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    grau CHAR(2) NOT NULL,
    codi CHAR(3) NOT NULL,
    codi_xtec CHAR(4) NOT NULL,

    CONSTRAINT CiclePlaEstudiPK PRIMARY KEY (cicle_pla_estudi_id),
    CONSTRAINT CPE_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id),
    CONSTRAINT CPE_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE MODUL_PLA_ESTUDI
(
    /* MPE */
    modul_pla_estudi_id INT NOT NULL AUTO_INCREMENT,
    modul_professional_id INT NOT NULL,
    cicle_pla_estudi_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    hores_setmana INT,
    especialitat VARCHAR(20),
    cos CHAR(1),
    es_fct BIT,

    CONSTRAINT ModulPlaEstudiPK PRIMARY KEY (modul_pla_estudi_id),
    CONSTRAINT MPE_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id),
    CONSTRAINT MPE_CiclePlaEstudiFK FOREIGN KEY (cicle_pla_estudi_id) REFERENCES CICLE_PLA_ESTUDI(cicle_pla_estudi_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE UNITAT_PLA_ESTUDI
(
    /* UPE */
    unitat_pla_estudi_id INT NOT NULL AUTO_INCREMENT,
    unitat_formativa_id INT NOT NULL,
    modul_pla_estudi_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    codi VARCHAR(5) NOT NULL,
    hores INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
    data_inici DATE,
    data_final DATE,
    orientativa BIT,
    es_fct BIT,

    CONSTRAINT UnitatPlaEstudiPK PRIMARY KEY (unitat_pla_estudi_id),
    CONSTRAINT UPE_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id),
    CONSTRAINT UPE_ModulPlaEstudiFK FOREIGN KEY (modul_pla_estudi_id) REFERENCES MODUL_PLA_ESTUDI(modul_pla_estudi_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/*
 * CreaPlaEstudisModul
 * Crea el pla d'estudis per a un mòdul (copiant unitats actives).
 * @param integer ModulPlaEstudiId Identificador del mòdul.
 */
DELIMITER //
CREATE PROCEDURE CreaPlaEstudisModul(IN ModulPlaEstudiId INT)
BEGIN
    DECLARE _unitat_formativa_id INT;
    DECLARE _nom VARCHAR(200);
    DECLARE _codi VARCHAR(5);
    DECLARE _hores INT;
    DECLARE _nivell INT;
    DECLARE _es_fct BIT;
    DECLARE done INT DEFAULT FALSE;

	BEGIN
		DECLARE cur CURSOR FOR 
		    SELECT unitat_formativa_id, nom, codi, hores, nivell, es_fct 
            FROM UNITAT_FORMATIVA 
            WHERE modul_professional_id in (SELECT modul_professional_id FROM MODUL_PLA_ESTUDI WHERE modul_pla_estudi_id=ModulPlaEstudiId)
			AND activa=1;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN cur;
		read_loop: LOOP
			FETCH cur INTO _unitat_formativa_id, _nom, _codi, _hores, _nivell, _es_fct ;
			IF done THEN
				LEAVE read_loop;
			END IF;
            INSERT INTO UNITAT_PLA_ESTUDI (unitat_formativa_id, modul_pla_estudi_id, nom, codi, hores, nivell, orientativa, es_fct ) 
                VALUES (_unitat_formativa_id, ModulPlaEstudiId, _nom, _codi, _hores, _nivell, 0, _es_fct );
		END LOOP;
		CLOSE cur;
	END;

END //
DELIMITER ;

/*
 * CreaPlaEstudisCicle
 * Crea el pla d'estudis per a un cicle (copiant mòduls i unitats actius).
 * @param integer CiclePlaEstudiId Identificador del cicle.
 */
DELIMITER //
CREATE PROCEDURE CreaPlaEstudisCicle(IN CiclePlaEstudiId INT)
BEGIN
    DECLARE _modul_professional_id INT;
    DECLARE _nom VARCHAR(200);
    DECLARE _codi VARCHAR(5);
    DECLARE _hores INT;
    DECLARE _hores_setmana INT;
    DECLARE _especialitat VARCHAR(20);
    DECLARE _cos CHAR(1);
    DECLARE _es_fct BIT;
    DECLARE done INT DEFAULT FALSE;

	BEGIN
		DECLARE cur CURSOR FOR 
		    SELECT modul_professional_id, nom, codi, hores, hores_setmana, especialitat, cos, es_fct 
            FROM MODUL_PROFESSIONAL 
            WHERE cicle_formatiu_id in (SELECT cicle_formatiu_id FROM CICLE_PLA_ESTUDI WHERE cicle_pla_estudi_id=CiclePlaEstudiId)
			AND actiu=1;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN cur;
		read_loop: LOOP
			FETCH cur INTO _modul_professional_id, _nom, _codi, _hores, _hores_setmana, _especialitat, _cos, _es_fct;
			IF done THEN
				LEAVE read_loop;
			END IF;
            INSERT INTO MODUL_PLA_ESTUDI (modul_professional_id, cicle_pla_estudi_id, nom, codi, hores, hores_setmana, especialitat, cos, es_fct) 
                VALUES (_modul_professional_id, CiclePlaEstudiId, _nom, _codi, _hores, _hores_setmana, _especialitat, _cos, _es_fct);
            SET @ModulPlaEstudiId = LAST_INSERT_ID();
			CALL CreaPlaEstudisModul(@ModulPlaEstudiId);
		END LOOP;
		CLOSE cur;
	END;

END //
DELIMITER ;

/*
 * CreaPlaEstudis
 * Crea el pla d'estudis per a un any acadèmic (copiant cicles, mòduls i unitats actius).
 * @param integer AnyAcademicId Identificador de l'any acadèmic.
 */
DELIMITER //
CREATE PROCEDURE CreaPlaEstudis(IN AnyAcademicId INT)
BEGIN
    DECLARE _cicle_formatiu_id INT;
    DECLARE _nom VARCHAR(200);
    DECLARE _grau CHAR(2);
    DECLARE _codi CHAR(3);
    DECLARE _codi_xtec CHAR(4);
    DECLARE done INT DEFAULT FALSE;

	BEGIN
		DECLARE cur CURSOR FOR SELECT cicle_formatiu_id, nom, grau, codi, codi_xtec FROM CICLE_FORMATIU WHERE actiu=1;
		DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
		OPEN cur;
		read_loop: LOOP
			FETCH cur INTO _cicle_formatiu_id, _nom, _grau, _codi, _codi_xtec;
			IF done THEN
				LEAVE read_loop;
			END IF;
            INSERT INTO CICLE_PLA_ESTUDI (cicle_formatiu_id, any_academic_id, nom, grau, codi, codi_xtec) 
                VALUES (_cicle_formatiu_id, AnyAcademicId, _nom, _grau, _codi, _codi_xtec);
            SET @CiclePlaEstudiId = LAST_INSERT_ID();
			CALL CreaPlaEstudisCicle(@CiclePlaEstudiId);
		END LOOP;
		CLOSE cur;
	END;

END //
DELIMITER ;
