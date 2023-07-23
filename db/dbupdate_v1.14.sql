/*
 * Actualització de la DB a partir de la versió 1.14
 */

/*
 * SuprimeixPropietatCSS
 * Suprimeix la propietat CSS especificada del camp d'una taula.
 * Ús: CALL SuprimeixPropietatCSS('MODUL_PLA_ESTUDI', 'metodologia', 'font-family:');
 * @param string Taula.
 * @param string Camp.
 * @param string Propietat.
 */
DELIMITER //
CREATE PROCEDURE SuprimeixPropietatCSS
(
    IN Taula VARCHAR(50), 
    IN Camp VARCHAR(50), 
    IN Propietat VARCHAR(50) 
)
BEGIN
    SET @SQL = CONCAT("UPDATE ", Taula, " SET ", Camp, "=");
    SET @LocatePropietat = CONCAT("locate('", Propietat, "', ", Camp, ")");
    SET @SubStr = CONCAT("substr(", Camp, ", ", @LocatePropietat, ", locate(';', ", Camp, ", ", @LocatePropietat, ")-", @LocatePropietat, "+1)"); 
    SET @SQL = CONCAT(@SQL, "replace(", Camp, ", ", @SubStr, ", '')");
    PREPARE stmt FROM @SQL;
    EXECUTE stmt;
END //
DELIMITER ;

DROP PROCEDURE CopiaTutors;

/*
 * CopiaTutors
 * Copia els tutors d'un any acadèmic a un altre.
 * @param integer AnyAcademicIdOrigen Identificador de l'any acadèmic origen.
 * @param integer AnyAcademicIdDesti Identificador de l'any acadèmic destí.
 */
DELIMITER //
CREATE PROCEDURE CopiaTutors(IN AnyAcademicIdOrigen INT, IN AnyAcademicIdDesti INT)
BEGIN
    DECLARE _curs_id, _professor_id, _curs_id_desti INT;
    DECLARE _grup_tutoria VARCHAR(2);
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
            SELECT T.curs_id, T.professor_id, T.grup_tutoria, 
            (   SELECT curs_id 
                FROM CURS C2 
                LEFT JOIN CICLE_PLA_ESTUDI CPE2 ON (CPE2.cicle_pla_estudi_id=C2.cicle_formatiu_id) 
                WHERE CPE2.cicle_formatiu_id=CPE.cicle_formatiu_id AND C2.nivell=C.nivell AND CPE2.any_academic_id=AnyAcademicIdDesti
            ) AS CursIdDesti
            FROM TUTOR T
            LEFT JOIN CURS C ON (C.curs_id=T.curs_id)
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=C.cicle_formatiu_id)
            WHERE CPE.any_academic_id=AnyAcademicIdOrigen;
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _curs_id, _professor_id, _grup_tutoria, _curs_id_desti;
            IF done THEN
                LEAVE read_loop;
            END IF;
            IF _curs_id_desti<>NULL THEN
                INSERT INTO TUTOR (curs_id, professor_id, grup_tutoria) VALUES (_curs_id_desti, _professor_id, _grup_tutoria);
            END IF;
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;

DROP PROCEDURE CopiaProgramacions;

/*
 * CopiaProgramacions
 * Copia les programacions d'un any acadèmic a un altre.
 * @param integer AnyAcademicIdOrigen Identificador de l'any acadèmic origen.
 * @param integer AnyAcademicIdDesti Identificador de l'any acadèmic destí.
 */
DELIMITER //
CREATE PROCEDURE CopiaProgramacions(IN AnyAcademicIdOrigen INT, IN AnyAcademicIdDesti INT)
BEGIN
    DECLARE _modul_pla_estudi_id, _modul_pla_estudi_id_desti INT;
    DECLARE _metodologia, _criteris_avaluacio, _recursos TEXT;
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
        SELECT MPE.modul_pla_estudi_id, MPE.metodologia, MPE.criteris_avaluacio, MPE.recursos,
            (   SELECT modul_pla_estudi_id 
                FROM MODUL_PLA_ESTUDI MPE2
                LEFT JOIN CICLE_PLA_ESTUDI CPE2 ON (CPE2.cicle_pla_estudi_id=MPE2.cicle_pla_estudi_id)
                WHERE MPE2.modul_professional_id=MPE.modul_professional_id AND CPE2.any_academic_id=AnyAcademicIdDesti
                LIMIT 1
            ) AS ModulPlaEstudiIdDesti			
            FROM MODUL_PLA_ESTUDI MPE
            LEFT JOIN CICLE_PLA_ESTUDI CPE ON (CPE.cicle_pla_estudi_id=MPE.cicle_pla_estudi_id)
            WHERE CPE.any_academic_id=AnyAcademicIdOrigen;        
        DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
        OPEN cur;
        read_loop: LOOP
            FETCH cur INTO _modul_pla_estudi_id, _metodologia, _criteris_avaluacio, _recursos, _modul_pla_estudi_id_desti;
            IF done THEN
                LEAVE read_loop;
            END IF;
            UPDATE MODUL_PLA_ESTUDI SET metodologia=_metodologia, criteris_avaluacio=_criteris_avaluacio, recursos=_recursos WHERE modul_pla_estudi_id=_modul_pla_estudi_id_desti;
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;


/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.15';
