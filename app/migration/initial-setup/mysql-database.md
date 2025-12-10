- [MySQL Database EXPORT](#mysql-database-export)
- [MySQL Database IMPORT](#mysql-database-import)
- [Setup and configure MySQL User](#setup-and-configure-mysql-user)
- [MySQL connection test](#mysql-connection-test)
- [zorg MySQL connection settings in PHP](#zorg-mysql-connection-settings-in-php)

# MySQL Database EXPORT
    $ mysqldump -u root -p zooomclan > mysqldb_zooomclan_export_yyyymmdd.sql

# MySQL Database IMPORT
## Connect to database
    $ mysql -u root -p
      mysql> CREATE DATABASE database_name;
      mysql> exit

## Import SQL DB-dump
    $ mysql -u root -p zooomclan < zooomclan_db_export.sql

# Setup and configure MySQL User
    $ mysql -u root -p
      mysql> CREATE USER '[username]'@'localhost' IDENTIFIED BY '[password]';
      mysql> GRANT ALL PRIVILEGES ON zooomclan.* TO 'zooomclan'@'localhost';
      mysql> FLUSH PRIVILEGES;
      mysql> exit

# MySQL connection test
    $ mysql -u [username] -p
      mysql> CONNECT zooomclan;
      mysql> SELECT * FROM chat LIMIT 0,1;

### Query result
```
+---------+---------------------+-------------+------+
| user_id | date                | from_mobile | text |
+---------+---------------------+-------------+------+
|       3 | 2004-08-30 17:20:00 |             | Test |
+---------+---------------------+-------------+------+
1 row in set (0.00 sec)
```

### Close connection
    mysql> exit

# zorg MySQL connection settings in PHP
    $ vi /var/www/includes/mysql_login.inc.local.php
      vi> a

### MySQL connection configuration
```
<?php
/** zorg MySQL Database login information */
define('MYSQL_HOST',    'localhost');
define('MYSQL_DBNAME',  'zooomclan');
define('MYSQL_DBUSER',  '[username]');
define('MYSQL_DBPASS',  "[password]");
```

    vi> <Esc>
    vi> :x<Return>
