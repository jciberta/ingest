# Entorn de desenvolupament

## Linux

```
apt update
apt install tasksel
tasksel install lamp-server
apt install php-zip php-mbstring
service apache2 restart
apt install mysql-workbench
```

Password de MySQL (depenent versió Ubuntu):
```
mysql_secure_installation
```

Per a les versions noves de MariaDB (MariaDB 10.4.3 and later):

```
$ sudo mysql -u root 
mysql> USE mysql;
mysql> UPDATE user SET plugin='mysql_native_password' WHERE User='root';
mysql> FLUSH PRIVILEGES;
mysql> exit;
$ service mysql restart
```

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



