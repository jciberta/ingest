/*
Actualització de la DB a partir de la versió 1.7
*/

ALTER TABLE SISTEMA ADD google_client_id VARCHAR(100);
ALTER TABLE SISTEMA ADD google_client_secret VARCHAR(100);
ALTER TABLE SISTEMA ADD google_redirect_uri VARCHAR(100);
ALTER TABLE SISTEMA ADD moodle_url VARCHAR(100);
ALTER TABLE SISTEMA ADD moodle_ws_token VARCHAR(100);
ALTER TABLE SISTEMA ADD ipdata_api_key VARCHAR(100);
