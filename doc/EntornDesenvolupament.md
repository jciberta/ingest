# InGest

## Entorn de desenvolupament

### Linux

```
apt update
apt install tasksel
tasksel install lamp-server
apt php-zip
apt install mysql-workbench
```

Password de MySQL (depenent versió Ubuntu):
```
sudo mysql_secure_installation
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

Còpia de seguretat
```
mysqldump -u root -p InGest > InGest.sql
mysqldump -u root -p InGest > "InGest_$(date +%F_%R).sql"
```

Càrrega de dades
```
mysql -u root -p InGest < InGest.sql
```

### Windows

#### XAMPP

##### MariaDB

Actualització del password (es pot fer amb el mateix MySQL Workbench):

```
SET PASSWORD FOR 'root'@'localhost' = PASSWORD('new_password');
```

##### MySQL

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



