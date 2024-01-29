/*
 * Actualització de la DB a partir de la versió 1.18
 */

ALTER TABLE MODUL_PLA_ESTUDI ADD seguiment TEXT;

ALTER TABLE UNITAT_PLA_ESTUDI ADD es_uf_addicional BIT(1) DEFAULT 0;

/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.19';
