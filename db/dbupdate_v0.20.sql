/*
Actualització de la DB a partir de la versió 0.20
*/

CREATE VIEW CURS_ACTUAL AS
	SELECT C.* 
    FROM CURS C 
    LEFT JOIN ANY_ACADEMIC AA ON (C.any_academic_id=AA.any_academic_id) 
    WHERE AA.actual=1
;

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
RETURNS VARCHAR(255)
BEGIN 
    RETURN TRIM(CONCAT(nom, ' ', cognom1, ' ', IFNULL(cognom2, '')));
END //
DELIMITER ;

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
RETURNS VARCHAR(255)
BEGIN 
    RETURN CONCAT(TRIM(CONCAT(cognom1, ' ', IFNULL(cognom2, ''))), ', ', nom);
END //
DELIMITER ;
