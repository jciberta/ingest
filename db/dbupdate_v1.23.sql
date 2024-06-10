/*
 * Actualització de la DB a partir de la versió 1.23
 */

ALTER TABLE CICLE_PLA_ESTUDI ADD url_aea_seguiment VARCHAR(255);



/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.24';