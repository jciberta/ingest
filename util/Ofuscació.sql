use InGest;

describe USUARI;
select * from USUARI;

update USUARI set username=concat(round(100000000*rand()),char(truncate(65+25*rand(),0))) where usuari_id>1;
update USUARI set codi=username where usuari_id>0;
update USUARI set document=username where usuari_id>0;
update USUARI set telefon=concat('6',truncate(100000000*rand(),0)) where usuari_id>0;

/* 1234 */ 
update USUARI set password="$2y$10$i9OI1gddsM2fK.r3d3nnFep3zNz5kuudM7E.TsLLAAVvNbaBca7Sa" where usuari_id>-1;

update USUARI set codi_postal="17230" where mod(usuari_id,7)=0 and usuari_id>1;
update USUARI set codi_postal="17251" where mod(usuari_id,7)=1 and usuari_id>1;
update USUARI set codi_postal="17200" where mod(usuari_id,7)=2 and usuari_id>1;
update USUARI set codi_postal="17220" where mod(usuari_id,7)=3 and usuari_id>1;
update USUARI set codi_postal="17100" where mod(usuari_id,7)=4 and usuari_id>1;
update USUARI set codi_postal="17250" where mod(usuari_id,7)=5 and usuari_id>1;
update USUARI set codi_postal="17225" where mod(usuari_id,7)=6 and usuari_id>1;

update USUARI set municipi="Palamós" where mod(usuari_id,7)=0 and usuari_id>1;
update USUARI set municipi="Calonge" where mod(usuari_id,7)=1 and usuari_id>1;
update USUARI set municipi="Palafrugell" where mod(usuari_id,7)=2 and usuari_id>1;
update USUARI set municipi="Sant Feliu de Guíxols" where mod(usuari_id,7)=3 and usuari_id>1;
update USUARI set municipi="La Bisbal" where mod(usuari_id,7)=4 and usuari_id>1;
update USUARI set municipi="Torroella de Montgrí" where mod(usuari_id,7)=5 and usuari_id>1;
update USUARI set municipi="Castell-Platja d'Aro" where mod(usuari_id,7)=6 and usuari_id>1;
update USUARI set poblacio=municipi where usuari_id>0;
update USUARI set municipi_naixement=municipi where usuari_id>0;

update USUARI set adreca=left(adreca, 6) where usuari_id>0;

update USUARI set sexe="H" where mod(usuari_id,2)=0 and usuari_id>1;
update USUARI set sexe="D" where mod(usuari_id,2)=1 and usuari_id>1;

update USUARI set nom="Jordi" where mod(usuari_id,19)=0 and usuari_id>1;
update USUARI set nom="Marc" where mod(usuari_id,19)=1 and usuari_id>1;
update USUARI set nom="David" where mod(usuari_id,19)=2 and usuari_id>1;
update USUARI set nom="Antoni" where mod(usuari_id,19)=3 and usuari_id>1;
update USUARI set nom="Daniel" where mod(usuari_id,19)=4 and usuari_id>1;
update USUARI set nom="Eva" where mod(usuari_id,19)=5 and usuari_id>1;
update USUARI set nom="Sílvia" where mod(usuari_id,19)=6 and usuari_id>1;
update USUARI set nom="Pau" where mod(usuari_id,19)=7 and usuari_id>1;
update USUARI set nom="Cristina" where mod(usuari_id,19)=8 and usuari_id>1;
update USUARI set nom="Maria" where mod(usuari_id,19)=9 and usuari_id>1;
update USUARI set nom="Joan" where mod(usuari_id,19)=10 and usuari_id>1;
update USUARI set nom="Montserrat" where mod(usuari_id,19)=11 and usuari_id>1;
update USUARI set nom="Aitor" where mod(usuari_id,19)=12 and usuari_id>1;
update USUARI set nom="Mònica" where mod(usuari_id,19)=13 and usuari_id>1;
update USUARI set nom="Carme" where mod(usuari_id,19)=14 and usuari_id>1;
update USUARI set nom="Josep" where mod(usuari_id,19)=15 and usuari_id>1;
update USUARI set nom="Pere" where mod(usuari_id,19)=16 and usuari_id>1;
update USUARI set nom="Marina" where mod(usuari_id,19)=17 and usuari_id>1;
update USUARI set nom="Yolanda" where mod(usuari_id,19)=18 and usuari_id>1;

update USUARI set cognom1="Garcia" where mod(usuari_id,17)=0 and usuari_id>1;
update USUARI set cognom1="Martínez" where mod(usuari_id,17)=1 and usuari_id>1;
update USUARI set cognom1="Sànchez" where mod(usuari_id,17)=2 and usuari_id>1;
update USUARI set cognom1="Rodríguez" where mod(usuari_id,17)=3 and usuari_id>1;
update USUARI set cognom1="Planes" where mod(usuari_id,17)=4 and usuari_id>1;
update USUARI set cognom1="López" where mod(usuari_id,17)=5 and usuari_id>1;
update USUARI set cognom1="Gómez" where mod(usuari_id,17)=6 and usuari_id>1;
update USUARI set cognom1="Moreno" where mod(usuari_id,17)=7 and usuari_id>1;
update USUARI set cognom1="Perez" where mod(usuari_id,17)=8 and usuari_id>1;
update USUARI set cognom1="Ruiz" where mod(usuari_id,17)=9 and usuari_id>1;
update USUARI set cognom1="González" where mod(usuari_id,17)=10 and usuari_id>1;
update USUARI set cognom1="Muñoz" where mod(usuari_id,17)=11 and usuari_id>1;
update USUARI set cognom1="Casas" where mod(usuari_id,17)=12 and usuari_id>1;
update USUARI set cognom1="Romero" where mod(usuari_id,17)=13 and usuari_id>1;
update USUARI set cognom1="Martí" where mod(usuari_id,17)=14 and usuari_id>1;
update USUARI set cognom1="Navarro" where mod(usuari_id,17)=15 and usuari_id>1;
update USUARI set cognom1="Ortega" where mod(usuari_id,17)=16 and usuari_id>1;

update USUARI set cognom2="Valls" where mod(usuari_id,7)=0 and usuari_id>1;
update USUARI set cognom2="Pla" where mod(usuari_id,7)=1 and usuari_id>1;
update USUARI set cognom2="Fortuny" where mod(usuari_id,7)=2 and usuari_id>1;
update USUARI set cognom2="Turó" where mod(usuari_id,7)=3 and usuari_id>1;
update USUARI set cognom2="Pujol" where mod(usuari_id,7)=4 and usuari_id>1;
update USUARI set cognom2="Coma" where mod(usuari_id,7)=5 and usuari_id>1;
update USUARI set cognom2="Tuc" where mod(usuari_id,7)=6 and usuari_id>1;

update USUARI set username=lower(concat(left(nom, 1), cognom1)) where es_professor=1 and usuari_id>1;
update USUARI set username=replace(username, "à", "a") where es_professor=1 and usuari_id>1;
update USUARI set username=replace(username, "á", "a") where es_professor=1 and usuari_id>1;
update USUARI set username=replace(username, "é", "e") where es_professor=1 and usuari_id>1;
update USUARI set username=replace(username, "í", "i") where es_professor=1 and usuari_id>1;
update USUARI set username=replace(username, "ó", "o") where es_professor=1 and usuari_id>1;
update USUARI set username=replace(username, "ñ", "n") where es_professor=1 and usuari_id>1;

update USUARI set email=concat(username,'@gmail.com') where usuari_id>0;
update USUARI set email_ins=concat(username,'@inspalamos.cat') where usuari_id>0;

update USUARI set data_ultim_login=NULL where usuari_id>0;
update USUARI set ip_ultim_login=NULL where usuari_id>0;
update USUARI set data_creacio=NULL where usuari_id>0;
update USUARI set data_modificacio=NULL where usuari_id>0;
update USUARI set nom_complet=NULL where usuari_id>0;
update USUARI set nacionalitat=NULL where usuari_id>0;

/* Data naixement */
update USUARI set data_naixement=concat(year(data_naixement), '-', truncate(1+11*rand(),0), '-', truncate(1+30*rand(),0)) where data_naixement is not null and usuari_id>1;

/* Notes */
update NOTES set nota1=truncate(5+4*rand(),0) where notes_id>0 and nota1>=5;
update NOTES set nota2=truncate(5+4*rand(),0) where notes_id>0 and nota2>=5;
update NOTES set nota3=truncate(5+4*rand(),0) where notes_id>0 and nota3>=5;
update NOTES set nota4=truncate(5+4*rand(),0) where notes_id>0 and nota4>=5;
update NOTES set nota5=truncate(5+4*rand(),0) where notes_id>0 and nota5>=5;

update NOTES set nota1=truncate(1+3*rand(),0) where notes_id>0 and nota1<5;
update NOTES set nota2=truncate(1+3*rand(),0) where notes_id>0 and nota2<5;
update NOTES set nota3=truncate(1+3*rand(),0) where notes_id>0 and nota3<5;
update NOTES set nota4=truncate(1+3*rand(),0) where notes_id>0 and nota4<5;
update NOTES set nota5=truncate(1+3*rand(),0) where notes_id>0 and nota5<5;

delete from REGISTRE where registre_id>0;
