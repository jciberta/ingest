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

/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.15';
