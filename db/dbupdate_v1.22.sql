/*
 * Actualització de la DB a partir de la versió 1.22
 */

ALTER TABLE MODUL_PROFESSIONAL ADD hores_fct INT;
ALTER TABLE MODUL_PLA_ESTUDI ADD hores_fct INT;

DROP FUNCTION FormataCognom1Cognom2Nom;
DROP FUNCTION FormataNomCognom1Cognom2;

/*
 * FormataCognom1Cognom2Nom
 *
 * Formata el nom d'una persona a l'estil CC,N.
 *
 * @param string Nom.
 * @param string Cognom1.
 * @param string Cognom2.
 * @return string Nom formatat CC,N.
 */
DELIMITER //
CREATE FUNCTION FormataCognom1Cognom2Nom(Nom VARCHAR(100), Cognom1 VARCHAR(100), Cognom2 VARCHAR(100))
RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
BEGIN 
    RETURN CONCAT(TRIM(CONCAT(IFNULL(cognom1, ''), ' ', IFNULL(cognom2, ''))), ', ', nom);
END //
DELIMITER ;

/*
 * FormataNomCognom1Cognom2
 *
 * Formata el nom d'una persona a l'estil NCC.
 *
 * @param string Nom.
 * @param string Cognom1.
 * @param string Cognom2.
 * @return string Nom formatat NCC.
 */
DELIMITER //
CREATE FUNCTION FormataNomCognom1Cognom2(Nom VARCHAR(100), Cognom1 VARCHAR(100), Cognom2 VARCHAR(100))
RETURNS VARCHAR(255) CHARSET utf8mb4 COLLATE utf8mb4_general_ci
BEGIN 
    RETURN TRIM(CONCAT(nom, ' ', IFNULL(cognom1, ''), ' ', IFNULL(cognom2, '')));
END //
DELIMITER ;
 
 
/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.23';