/*
Actualització de la DB a partir de la versió 1.5
*/

ALTER TABLE UNITAT_PLA_ESTUDI ADD lms CHAR(1) NOT NULL DEFAULT 'M'; /* Moodle, Classroom */
ALTER TABLE UNITAT_PLA_ESTUDI ADD nota_maxima INT NOT NULL DEFAULT 100; /* Nota sobre 100 */
ALTER TABLE UNITAT_PLA_ESTUDI ADD nota_inferior_5 CHAR(1) NOT NULL DEFAULT 'T'; /* Trunca, Arrodoneix */
ALTER TABLE UNITAT_PLA_ESTUDI ADD nota_superior_5 CHAR(1) NOT NULL DEFAULT 'A'; /* Trunca, Arrodoneix */
ALTER TABLE UNITAT_PLA_ESTUDI ADD categoria_moodle_text VARCHAR(50); 

CREATE TABLE TIPUS_MATERIAL
(
    /* TM */
    tipus_material_id INT NOT NULL AUTO_INCREMENT,
    nom VARCHAR(200) NOT NULL,

    CONSTRAINT TipusMaterialPK PRIMARY KEY (tipus_material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE MATERIAL
(
    /* M */
    material_id INT NOT NULL,
    tipus_material_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
	codi VARCHAR(15) NOT NULL,
    descripcio TEXT,
    data_compra DATE,
    es_obsolet BIT NOT NULL DEFAULT 0,
	
	CONSTRAINT MaterialPK PRIMARY KEY (material_id),
	CONSTRAINT M_TipusMaterialFK FOREIGN KEY (tipus_material_id) REFERENCES TIPUS_MATERIAL(tipus_material_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE RESERVA_MATERIAL
(
    /* RM */
    reserva_material_id INT NOT NULL,
    material_id INT NOT NULL,
	usuari_id INT NOT NULL,
    data_sortida DATE,
    data_entrada DATE,
    nota VARCHAR(200),
	
	CONSTRAINT ReservaMaterialPK PRIMARY KEY (reserva_material_id),
	CONSTRAINT RM_MaterialFK FOREIGN KEY (material_id) REFERENCES MATERIAL(material_id),
    CONSTRAINT RM_UsuariFK FOREIGN KEY (usuari_id) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
