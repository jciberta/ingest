/*
Actualització de la DB a partir de la versió 0.14
*/

CREATE TABLE NOTES_MP
(
    /* NMP */
    notes_mp_id INT NOT NULL AUTO_INCREMENT,
    matricula_id INT NOT NULL, 
    modul_professional_id INT NOT NULL,
    nota INT CHECK (nota IN (1, 2, 3, 4, 5, 6, 7, 8, 9, 10, -1, 100, -100)),

    CONSTRAINT NotesMPPK PRIMARY KEY (notes_mp_id),
    CONSTRAINT NMP_MatriculaFK FOREIGN KEY (matricula_id) REFERENCES MATRICULA(matricula_id),
    CONSTRAINT NMP_ModulProfessionalFK FOREIGN KEY (modul_professional_id) REFERENCES MODUL_PROFESSIONAL(modul_professional_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
