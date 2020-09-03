/*
Actualització de la DB a partir de la versió 0.24
*/

ALTER TABLE MODUL_PROFESSIONAL ADD es_fct BIT;

ALTER TABLE UNITAT_FORMATIVA ADD es_fct BIT;

CREATE TABLE PROJECTE
(
    /* PRJ */
    projecte_id INT NOT NULL AUTO_INCREMENT,
    codi VARCHAR(50) NOT NULL,
    nom VARCHAR(200) NOT NULL,
    tipus CHAR(2) NOT NULL, /* E+ */
    descripcio TEXT,
    periode VARCHAR(10) NOT NULL,
    data_inici DATE,
    data_final DATE,
    coordinador_id INT NOT NULL,

    CONSTRAINT ProjectePK PRIMARY KEY (projecte_id),
    CONSTRAINT PRJ_UsuariFK FOREIGN KEY (coordinador_id) REFERENCES USUARI(usuari_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
