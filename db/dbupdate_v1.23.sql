/*
 * Actualització de la DB a partir de la versió 1.23
 */

ALTER TABLE CICLE_PLA_ESTUDI ADD url_aea_seguiment VARCHAR(255);

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
    DECLARE _metodologia, _criteris_avaluacio, _recursos, _unitats_didactiques TEXT;
    DECLARE done INT DEFAULT FALSE;

    BEGIN
        DECLARE cur CURSOR FOR
        SELECT MPE.modul_pla_estudi_id, MPE.metodologia, MPE.criteris_avaluacio, MPE.recursos, MPE.unitats_didactiques,
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
            FETCH cur INTO _modul_pla_estudi_id, _metodologia, _criteris_avaluacio, _recursos, _unitats_didactiques, _modul_pla_estudi_id_desti;
            IF done THEN
                LEAVE read_loop;
            END IF;
            UPDATE MODUL_PLA_ESTUDI SET metodologia=_metodologia, criteris_avaluacio=_criteris_avaluacio, recursos=_recursos, unitats_didactiques=_unitats_didactiques WHERE modul_pla_estudi_id=_modul_pla_estudi_id_desti;
        END LOOP;
        CLOSE cur;
    END;
END //
DELIMITER ;


/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.24';