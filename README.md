# ADACT
Alignment-free Dissimilarity Analysis & Comparison Tool;
ADACT in short. This tool produces the distance matrix,
species relation, phylogenetic trees
based on a number of indices.

## Installation

Applied for both macOS & Linux.

php 5.6.1+ is required, supports php 7+.

#### Edit Config.php
Edit the `Config.php` located at the root of the project,
otherwise mail client, mysql server may not work. At least
the following constants are needed to be edited:
```php
Interface Config{
    const MYSQL_USER = 'root';
    const MYSQL_PASS = 'root';
    const MYSQL_DB   = 'adact';
    .
    .
    .
    const MAIL_USER = 'example@gmail.com';
    const MAIL_PASS = 'example_password';
    const MAIL_FROM = 'example@gmail.com';
    .
    .
    .
}
```

#### Enable Cron Job for running pending project(s)
```bash
bash ./scripts/add_to_crontab.sh
```

#### Run development server
```bash
php -S 127.0.0.1:8080 ./router.php
```
(Also works with Apache2 when `htaccess` and `modrewrite` are enabled)

#### Enable timezone to mysql
```bash
mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root -p mysql
```

#### Enable directory writing for ./Projects and ./tmp
(This process is only applied to Linux distributions with Apache2 Server)

[Help Link](https://stackoverflow.com/a/16373988/4147849)
1. Check which `user` is running:
    ```bash
    ps aux
    ```
2. Add that user (assuming `www-data`) to the group:
    ```bash
    sudo chgrp -R  www-data ./ && sudo chmod -R g+w ./
    ```

#### SQL files
See the README file inside the `sql` folder. Current sql file is `adact_v4.3.sql`.

#### Sample input files
Sample files are located in the `Sample Input Files` folder

## Contributing
For the sake of convenience, two scripts have been added in the `scripts` folder.
- `./scripts/deploy.sh <sql_version>` : Run this before committing as it'll switch the configuration file
  (ie. `Config.php`) to the factory mode, as well as take care of DB.
  It takes one argument (sql version number) as it backs up the new sql file
  automatically.
- `./scripts/revert.sh` : Revert the configuration file to the one that you were using previously (if you were)

_Use the project root as present working directory when running the above scripts._

## Notes
- Default project directory: `./Projects` (you can change this in the `Config.php` but it's not recommended)
- Default temporary directory: `./tmp` (DON'T change this to `/tmp`, this is done on purpose)
- 

## Not Implemented
- Change user info (other than password)
- Delete account

