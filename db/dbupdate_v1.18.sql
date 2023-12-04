/*
 * Actualització de la DB a partir de la versió 1.18
 */

ALTER TABLE MODUL_PLA_ESTUDI ADD seguiment TEXT;


/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.19';
