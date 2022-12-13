# Entorn de desenvolupament

## Estructura

Dues carpetes principals:

  * Codi: /var/www/html
  * Dades: /var/www/ingest-data (root/root 755)
    *  /var/www/ingest-data/pdf (www-data/www-data 755)
    *  /var/www/ingest-data/pix (www-data/www-data 755)
    *  /var/www/ingest-data/upload (root/root 777)

## Linux

### Requisits

  * Ubuntu 22.04
  * Apache 2.4
  * PHP 8.1
  * MySQL 8.0

### Instal·lació

```
sudo apt update && sudo apt upgrade -y
sudo apt install openssh-server zip
sudo apt install apache2 mysql-server php libapache2-mod-php php-mysql php-cli php-zip php-curl php-xml php-mbstring msttcorefonts

sudo mysql
ALTER USER 'root'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'XXX';
exit;
mysql -u root -p
create database InGest;
SET GLOBAL log_bin_trust_function_creators = 1;
exit;
mysql -u root -p InGest < InGest.sql
```

### HTTPS

```
nano /etc/apache2/sites-available/default-ssl.conf
    ...
    SSLEngine On
    SSLCertificateFile 
    SSLCertificateKeyFile
    ...
a2enmod ssl
a2ensite default-ssl
service apache2 reload
```

Forçar HTTPS:

```
nano /etc/apache2/sites-available/000-default.conf
    <VirtualHost *:80>
        <Location "/">
            Redirect permanent "https://%{HTTP_HOST}%{REQUEST_URI}"
        </Location>
    ...
service apache2 restart
```

### Servidor de correu

Relay amb GMail (http://www.lotar.altervista.org/wiki/en/how-to/sendmail-and-gmail-relay)

```
apt-get install sendmail mailutils
cd /etc/mail
cp sendmail.cf sendmail.cf.orig
cp sendmail.mc sendmail.mc.orig
mkdir -m 700 -p /etc/mail/auth
nano /etc/mail/auth/auth-info
    AuthInfo:smtp.gmail.com "U:SRVINGEST" "I:no.contesteu@inspalamos.cat" "P:XXX"
cd /etc/mail/auth
makemap hash auth-info < auth-info
chmod 0600 /etc/mail/auth/*
```

Before the first MAILER_DEFINITIONS line:

```
nano /etc/mail/sendmail.mc
    define(`SMART_HOST',`smtp.gmail.com')dnl
    define(`RELAY_MAILER_ARGS', `TCP $h 587')dnl
    define(`ESMTP_MAILER_ARGS', `TCP $h 587')dnl
    define(`confAUTH_MECHANISMS', `EXTERNAL GSSAPI DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')dnl
    FEATURE(`authinfo',`hash /etc/mail/auth/auth-info')dnl
    TRUST_AUTH_MECH(`EXTERNAL DIGEST-MD5 CRAM-MD5 LOGIN PLAIN')
cd /etc/mail
m4 sendmail.mc > sendmail.cf
service sendmail restart
service sendmail status
echo 'e-Mail TEST' | mail -s 'Backup SRVINGEST' test@inspalamos.cat
```

### Còpia de seguretat


### Pàgina web

Pàgina web inicial

```
nano /var/www/html/index.html

<!DOCTYPE HTML>
<html lang="en-US">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="refresh" content="0; url=ingest/index.php">
        <script type="text/javascript">
            window.location.href = "ingest/index.php"
        </script>
        <title>Page Redirection</title>
    </head>
    <body>
    </body>
</html>

```


### Composer

Instal·lació:
```
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('sha384', 'composer-setup.php') === '906a84df04cea2aa72f40b5f787e49f22d4c2f19492ac310e8cba5b96ac8b64115ac402c8cd292b8a03482574915d1a8') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"
mv composer.phar /usr/local/bin/composer
```

### PHPDocumentator

Instal·lació:
```
wget http://phpdoc.org/phpDocumentor.phar
chmod +x phpDocumentor.phar
mv phpDocumentor.phar /usr/local/bin/phpdoc
phpdoc --version
```

### GIT

git config --global user.name "nom"
git config --global user.email "correu"

### Entorn

```
mkdir /var/www/ingest-data/pdf -p
mkdir /var/www/ingest-data/pix -p
mkdir /var/www/ingest-data/upload -p
chmod 755 /var/www/ingest-data/pdf
chmod 755 /var/www/ingest-data/pix 
chmod 777 /var/www/ingest-data/upload 
chown www-data /var/www/ingest-data/pdf
chown www-data /var/www/ingest-data/pix
chgrp www-data /var/www/ingest-data/pdf
chgrp www-data /var/www/ingest-data/pix
ls -al /var/www/ingest-data
```

### Altres

Accés a BD (si no és té accés):
```
GRANT ALL PRIVILEGES ON InGest.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

Connexió:
```
mysql -u root -p
mysql -u root -p -h 127.0.0.1 -P 3306
```

Còpia de seguretat:
```
mysqldump -u root -p --routines InGest > InGest.sql
mysqldump -u root -p --routines InGest > "InGest_$(date +%F_%R).sql"
```

Càrrega de dades:
```
mysql -u root -p InGest < InGest.sql
```


## Windows

### XAMPP

#### MariaDB

Actualització del password (es pot fer amb el mateix MySQL Workbench):

```
SET PASSWORD FOR 'root'@'localhost' = PASSWORD('new_password');
```

#### MySQL

XAMPP - Replacing MariaDB with MySQL
* https://odan.github.io/2017/08/13/xampp-replacing-mariadb-with-mysql.html
* https://gist.github.com/odan/c799417460470c3776ffa8adce57eece

No copiar la carpeta Data de MariaDB i fer a la carpeta Data:

```
mysqld --initialize-insecure (with blank root password)
mysql -u root -p
UPDATE mysql.user
    SET authentication_string = PASSWORD('root'), password_expired = 'N'
    WHERE User = 'root' AND Host = 'localhost';
FLUSH PRIVILEGES;
```



