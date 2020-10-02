# ADACT
Alignment-free Dissimilarity Analysis & Comparison Tool; ADACT in short. This tool produces the distance matrix, species
relation, phylogenetic trees based on a number of indices.

## Installation

Applied for both macOS & Linux.

php 5.6.1+ is required, supports php 7+.

#### Edit Config.php
Edit the `Config.php` located at the root of the project, otherwise mail client, mysql server may not work. At least the
following constants are needed to be edited:
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
    const WEB_ADDRESS = 'www.example.com/ADACT';
    const WEB_DIRECTORY = '/ADACT/'; // Notice the trailing slash
}
```

In `Config.php` file, you can also increase/decrease the following limitations:
- Maximum file upload size (by editing `MAX_UPLOAD_SIZE` constant)
- The size of each FASTA sequence (by editing `MAX_FILE_SIZE` constant)
- Maximum FASTA sequences allowed (by editing `MAX_FILE_ALLOWED` constant)

You can also use `INF` (infinity) constant instead of integers in the above constants to remove any limitations. But
beware that `MAX_UPLOAD_SIZE` doesn't modify `php.ini` file, it only checks the uploaded file size. If you need to
increase upload file size, you'll need to [edit](https://stackoverflow.com/a/2184541/4147849) `php.ini` file.

#### Enable Cron Job for running pending project(s)
```bash
bash ./scripts/add_to_crontab.sh
```

#### Run development server
```bash
php -S 127.0.0.1:8080 ./router.php
```
(Also works with Apache2 when `htaccess` and `modrewrite` are enabled)

#### Install GNU time
GNU time is used to measure CPU and RAM usage.

On Ubuntu: (you'll need root privilege)
```bash
apt-get install time
```
On CentOS:
```bash
yum install time
```
On macOS: (using Homebrew)
```bash
brew install gnu-time
```

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
See the README file inside the `sql` folder. Current sql file is `adact_v4.5.sql`.

#### Sample input files
Sample files are located in the `Sample Input Files` folder

## Dataset
The following two public datasets have been experimented on ADACT:
- [AFProject](http://afproject.org/app/)
- [GDS Dataset](https://www.cs.kent.ac.uk/projects/biasprofs/downloads.html)

## Contributing
For the sake of convenience, two scripts have been added in the `scripts` folder.
- `./scripts/deploy.sh <sql_version>` : Run this before committing as it'll switch the configuration file (ie.
  `Config.php`) to the factory mode, as well as take care of DB. It takes one argument (sql version number) as it backs
   up the new sql file automatically. Be sure to change the mysql password in the `deploy.sh` file.
- `./scripts/revert.sh` : Revert the configuration file to the one that you were using previously (if you were)

_Use the project root as present working directory when running the above scripts._

## Notes
- Default project directory: `./Projects` (you can change this in the `Config.php` but it's not recommended)
- Default temporary directory: `./tmp` (DON'T change this to `/tmp`, this is done on purpose)

## Not Implemented
- Change user info (other than password)
- Delete account
