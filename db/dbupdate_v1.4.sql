/*
Actualització de la DB a partir de la versió 1.4
*/

ALTER TABLE USUARI ADD
	titol_angles VARCHAR(5);
ALTER TABLE USUARI ADD
	perfil_aicle BIT DEFAULT 0;

ALTER TABLE REGISTRE ADD registre_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY;
	
CREATE TABLE GEOLOCALITZACIO_IP
(
    /* GIP */
    ip VARCHAR(15) NOT NULL,
    is_eu BIT,
    city VARCHAR(100),
    region VARCHAR(100),
    region_code CHAR(2),
    country_name VARCHAR(100),
    country_code CHAR(2),
    latitude REAL,
    longitude REAL,
    postal VARCHAR(10),
    calling_code VARCHAR(5),
    flag_url VARCHAR(100),
    asn VARCHAR(20),
    asn_name VARCHAR(100),
    asn_domain VARCHAR(50),
    asn_route VARCHAR(20),
    asn_type VARCHAR(5),
    is_tor BIT,
    is_proxy BIT,
    is_anonymous BIT,
    is_known_attacker BIT,
    is_known_abuser BIT,
    is_threat BIT,
    is_bogon BIT,
    data_modificacio DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	
    CONSTRAINT GeolocalitzacioIPPK PRIMARY KEY (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
