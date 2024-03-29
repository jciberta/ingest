
/* Detecció apuntadors fora convocatòria */

SELECT distinct(matricula_id) FROM NOTES WHERE nota1 is null AND convocatoria>1;
SELECT distinct(matricula_id) FROM NOTES WHERE nota2 is null AND convocatoria>2;
SELECT distinct(matricula_id) FROM NOTES WHERE nota3 is null AND convocatoria>3;
SELECT distinct(matricula_id) FROM NOTES WHERE nota4 is null AND convocatoria>4;
SELECT distinct(matricula_id) FROM NOTES WHERE nota5 is null AND convocatoria>5;

/* Detecció buits entre notes (s'ha d'arreglar a mà) */

select * from NOTES where nota1 is NULL and (nota2 is not null or nota3 is not null or nota4 is not null or nota5 is not null);
select * from NOTES where nota2 is NULL and (nota3 is not null or nota4 is not null or nota5 is not null);
select * from NOTES where nota3 is NULL and (nota4 is not null or nota5 is not null);
select * from NOTES where nota4 is NULL and (nota5 is not null);


/*
	MySQL WorkBench:
    You are using safe update mode and you tried to update a table without a WHERE that uses a KEY column To disable safe mode, 
    toggle the option in Preferences -> SQL Editor and reconnect.	
*/

/* Correcció apuntadors convocatòria */

UPDATE NOTES SET convocatoria=0
	WHERE (nota1 is not null AND nota1>=5)
	OR (nota2 is not null AND nota2>=5)
	OR (nota3 is not null AND nota3>=5)
	OR (nota4 is not null AND nota4>=5)
	OR (nota5 is not null AND nota5>=5);

UPDATE NOTES SET convocatoria=1 
	WHERE nota1 is null;

UPDATE NOTES SET convocatoria=2 
	WHERE nota2 is null 
	AND nota1 is not null AND nota1<5;

UPDATE NOTES SET convocatoria=3 
	WHERE nota3 is null 
	AND nota2 is not null AND nota2<5
	AND nota1 is not null AND nota1<5;

UPDATE NOTES SET convocatoria=4
	WHERE nota4 is null 
	AND nota3 is not null AND nota3<5
	AND nota2 is not null AND nota2<5
	AND nota1 is not null AND nota1<5;

UPDATE NOTES SET convocatoria=5
	WHERE nota5 is null 
	AND nota4 is not null AND nota4<5
	AND nota3 is not null AND nota3<5
	AND nota2 is not null AND nota2<5
	AND nota1 is not null AND nota1<5;
