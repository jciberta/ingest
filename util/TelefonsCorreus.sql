
/* Correcció text dels telèfons importats del SAGA */

UPDATE USUARI SET telefon = REPLACE(telefon, 'Ã¨', 'è');
UPDATE USUARI SET telefon = REPLACE(telefon, '(Primer telèfon de l\'alumne)', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '(Primer telèfon de l)', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '(Primer telèfon)', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '(Telèfon principal)', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '(Correu electrònic principal)', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '(Mòbil)', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '()', '');
UPDATE USUARI SET telefon = REPLACE(telefon, 'T +34-', '');
UPDATE USUARI SET telefon = REPLACE(telefon, '+34-', '');
UPDATE USUARI SET telefon = REPLACE(telefon, 'T ', '');
UPDATE USUARI SET telefon = REPLACE(telefon, ' ,', ',');
UPDATE USUARI SET telefon = TRIM(telefon);


/* Comprovació correus ben posats */
select * from USUARI where email like '%@inspalamos.cat';
select * from USUARI where email_ins not like '%@inspalamos.cat';

select * from USUARI where email like '%@inspalamos.cat';
update USUARI set email_ins=email where email like '%@inspalamos.cat';
update USUARI set email=NULL where email like '%@inspalamos.cat';

select * from USUARI where email_ins not like '%@inspalamos.cat';
update USUARI set email=email_ins where email is not null and email_ins not like '%@inspalamos.cat';
