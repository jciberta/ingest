/*
Actualització de la DB a partir de la versió 0.25
*/

ALTER TABLE CURS ADD grups_classe VARCHAR(100);
ALTER TABLE CURS ADD grups_tutoria VARCHAR(100);

CREATE TABLE PASSWORD_RESET_TEMP
(
    /* PRT */
    email           VARCHAR(250) NOT NULL,
    clau            VARCHAR(250) NOT NULL,
    data_expiracio  DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
