/*
 * Actualització de la DB a partir de la versió 1.19
 */

ALTER TABLE UNITAT_PLA_ESTUDI ADD es_uf_addicional BIT(1) DEFAULT 0;

/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.20';
