/*
Actualització de la DB a partir de la versió 1.12
*/

CREATE TABLE PREU_MATRICULA 
(
    /* PM */
    preu_matricula_id INT NOT NULL AUTO_INCREMENT,
    any_academic_id INT NOT NULL,
    cicle_formatiu_id INT NOT NULL,
    nivell INT CHECK (nivell IN (1, 2)),
    nom VARCHAR(20) NOT NULL,
    preu REAL NOT NULL,
    numero_uf INT NOT NULL,

    CONSTRAINT PreuMatriculaPK PRIMARY KEY (preu_matricula_id),
    CONSTRAINT PM_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT PM_CicleFormatiuFK FOREIGN KEY (cicle_formatiu_id) REFERENCES CICLE_FORMATIU(cicle_formatiu_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE BONIFICACIO_MATRICULA 
(
    /* BM */
    bonificacio_matricula_id INT NOT NULL AUTO_INCREMENT,
    any_academic_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    valor REAL NOT NULL,
    tipus CHAR NOT NULL, /* Percentatge, Euros */
    unitat_formativa_id INT NULL,

    CONSTRAINT BonificacioMatriculaPK PRIMARY KEY (bonificacio_matricula_id),
    CONSTRAINT BM_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT BM_UnitatFormativaFK FOREIGN KEY (unitat_formativa_id) REFERENCES UNITAT_FORMATIVA(unitat_formativa_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
