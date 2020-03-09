/*
Actualització de la DB a partir de la versió 0.23
*/

DROP TABLE PROFESSOR_DEPARTAMENT;

DROP TABLE DEPARTAMENT;

CREATE TABLE EQUIP
(
    /* EQP */
    equip_id INT NOT NULL AUTO_INCREMENT,
	any_academic_id INT NOT NULL,
    tipus CHAR(2), /* DP: Departament, EQ: Equip docent, CM: Comissió */
    nom VARCHAR(50) NOT NULL,
    cap INT NULL,

    CONSTRAINT EquipPK PRIMARY KEY (equip_id),
    CONSTRAINT EQP_AnyAcademicFK FOREIGN KEY (any_academic_id) REFERENCES ANY_ACADEMIC(any_academic_id),
    CONSTRAINT EQP_CapFK FOREIGN KEY (cap) REFERENCES USUARI(usuari_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE PROFESSOR_EQUIP
(
    /* PEQ */
    professor_equip_id INT NOT NULL AUTO_INCREMENT,
    professor_id INT NOT NULL,
    equip_id INT NOT NULL,

    CONSTRAINT ProfessorEquipPK PRIMARY KEY (professor_equip_id),
    CONSTRAINT PEQ_UsuariFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id),
    CONSTRAINT PEQ_DepartamentFK FOREIGN KEY (equip_id) REFERENCES EQUIP(equip_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
