/*
 * Actualització de la DB a partir de la versió 1.19
 */

ALTER TABLE UNITAT_PLA_ESTUDI ADD es_uf_addicional BIT(1) DEFAULT 0;

ALTER TABLE DOCUMENT MODIFY solicitant CHAR(1); /* Tutor, Alumne */
ALTER TABLE DOCUMENT MODIFY lliurament CHAR(2); /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */
ALTER TABLE DOCUMENT MODIFY custodia CHAR(2); /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */

DROP FUNCTION FormataNomCognom1Cognom2;

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
    RETURN TRIM(CONCAT(nom, ' ', IFNULL(cognom1, ''), ' ', IFNULL(cognom2, '')));
END //
DELIMITER ;

DROP FUNCTION FormataCognom1Cognom2Nom;

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
    RETURN CONCAT(TRIM(CONCAT(IFNULL(cognom1, ''), ' ', IFNULL(cognom2, ''))), ', ', nom);
END //
DELIMITER ;


/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.20';
