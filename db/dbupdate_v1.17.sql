/*
 * Actualització de la DB a partir de la versió 1.17
 */

DROP TABLE DOCUMENT;

CREATE TABLE DOCUMENT (
    /* D */
    document_id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(255) NOT NULL,
    estudi CHAR(3) NOT NULL DEFAULT 'GEN' CHECK (estudi IN ('GEN', 'ESO', 'BAT', 'CF0,', 'CFB', 'CFM', 'CFS')),
    subestudi CHAR(3), /* FPB, APD, CAI, DAM, FIP, HBD, SMX, ... */
    categoria CHAR(1), /* Document de centre, Imprès de funcionament */
    solicitant CHAR(1) NOT NULL, /* Tutor, Alumne */
    lliurament CHAR(2) NOT NULL, /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */
    custodia CHAR(2) NOT NULL, /* TUtor, Tutor Fct, Tutor Dual, SEcretaria, Cap Estudis, Coordinador Fp, Coordinador Dual */
    observacions TEXT NOT NULL,
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    CONSTRAINT DocumentPK PRIMARY KEY (document_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE DOCUMENT_VERSIO (
    /* DV */
    document_versio_id INT NOT NULL AUTO_INCREMENT,
    document_id INT NOT NULL,
    versio INT NOT NULL DEFAULT 0,
    descripcio_modificacio VARCHAR(255) NOT NULL,
    enllac VARCHAR(255) NOT NULL,
    estat char(1) NOT NULL DEFAULT 'E' CHECK (estat IN ('E', 'R', 'V', 'A')), /* Elaboració, Realitzat, reVisió, Aprovat */
    data_creacio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    usuari_realitzat INT,
    data_realitzat DATE,
    usuari_revisat INT,
    data_revisat DATE,
    usuari_aprovat INT,
    data_aprovat DATE,

	CONSTRAINT DocumentPK PRIMARY KEY (document_versio_id),
    CONSTRAINT DV_DocumentFK FOREIGN KEY (document_id) REFERENCES DOCUMENT(document_id),
    CONSTRAINT DV_UsuariRealitzatFK FOREIGN KEY (usuari_realitzat) REFERENCES USUARI(usuari_id),
    CONSTRAINT DV_UsuariRevisatFK FOREIGN KEY (usuari_revisat) REFERENCES USUARI(usuari_id),
    CONSTRAINT DV_UsuariAprovatFK FOREIGN KEY (usuari_aprovat) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


/* Aplicar a producció */
UPDATE SISTEMA SET versio_db='1.18';
