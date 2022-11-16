/*
Actualització de la DB a partir de la versió 1.11
*/

ALTER TABLE NOTES ADD nota_t1 INT;
ALTER TABLE NOTES ADD nota_t2 INT;
ALTER TABLE NOTES ADD nota_t3 INT;

DELIMITER //
CREATE TRIGGER AU_CopiaTrimestre AFTER UPDATE ON CURS FOR EACH ROW
BEGIN
    IF NEW.trimestre = 2 AND OLD.trimestre = 1 THEN
        /* Pas del 1r trimestre al 2n */
        UPDATE NOTES set nota_t1 = ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) WHERE matricula_id IN (SELECT matricula_id FROM MATRICULA WHERE curs_id=OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Pas del 1r trimestre al 2n. curs_id=', OLD.curs_id));
    ELSEIF NEW.trimestre = 3 AND OLD.trimestre = 2 THEN
        /* Pas del 2n trimestre al 3r */
        UPDATE NOTES set nota_t2 = ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) WHERE matricula_id IN (SELECT matricula_id FROM MATRICULA WHERE curs_id=OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Pas del 2n trimestre al 3r. curs_id=', OLD.curs_id));
	ELSEIF NEW.avaluacio = 'EXT' AND OLD.avaluacio = 'ORD' THEN
        /* Pas del 3r trimestre a extraordinària */
        UPDATE NOTES set nota_t3 = ObteNotaConvocatoria(nota1, nota2, nota3, nota4, nota5, convocatoria) WHERE matricula_id IN (SELECT matricula_id FROM MATRICULA WHERE curs_id=OLD.curs_id);
        INSERT INTO REGISTRE (usuari_id, nom_usuari, data, ip, seccio, missatge)
            VALUES (1, 'Taula CURS', now(), '127.0.0.1', 'Trigger', CONCAT('Pas del 3r trimestre a extraordinària. curs_id=', OLD.curs_id));
    END IF;
END //
DELIMITER ;