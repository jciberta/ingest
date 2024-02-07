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

/*
 * ObteNotaConvocatoriaI0
 *
 * Retorna la nota corresponent a la convocatòria actual, i també si està aprovada (convocatòria=0).
 *
 * Ús:
 *   SELECT notes_id, ObteNotaConvocatoriaI0(nota1, nota2, nota3, nota4, nota5, convocatoria) FROM NOTES;
 *
 * @param integer nota1 Nota de la 1a convocatòria.
 * @param integer nota2 Nota de la 2a convocatòria.
 * @param integer nota3 Nota de la 3a convocatòria.
 * @param integer nota4 Nota de la 4a convocatòria.
 * @param integer nota5 Nota de la 5a convocatòria.
 * @param integer convocatoria Convocatòria actual.
 * @return integer Nota corresponent a la convocatòria actual.
 */
DELIMITER //
CREATE FUNCTION ObteNotaConvocatoriaI0(nota1 INT, nota2 INT, nota3 INT, nota4 INT, nota5 INT, convocatoria INT)
RETURNS INT
BEGIN 
    DECLARE Nota INT;
    IF convocatoria = 5 THEN SET Nota = nota5;
    ELSEIF convocatoria = 4 THEN SET Nota = nota4;
    ELSEIF convocatoria = 3 THEN SET Nota = nota3;
    ELSEIF convocatoria = 2 THEN SET Nota = nota2;
    ELSEIF convocatoria = 1 THEN SET Nota = nota1;
    ELSEIF convocatoria = 0 THEN SET Nota = IFNULL(nota5, IFNULL(nota4, IFNULL(nota3, IFNULL(nota2, IFNULL(nota1, NULL)))));
    ELSE SET Nota = NULL;
    END IF;
    RETURN Nota;
END //
DELIMITER ;






/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.20';
