/*
Actualització de la DB a partir de la versió 0.13
*/

CREATE TABLE TUTOR
(
    /* TUT */
    tutor_id INT NOT NULL AUTO_INCREMENT,
    curs_id INT NOT NULL, 
    professor_id INT NOT NULL,
    grup_tutoria VARCHAR(2),

    CONSTRAINT TutorPK PRIMARY KEY (tutor_id),
    CONSTRAINT TUT_CursFK FOREIGN KEY (curs_id) REFERENCES CURS(curs_id),
    CONSTRAINT TUT_ProfessorFK FOREIGN KEY (professor_id) REFERENCES USUARI(usuari_id) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
