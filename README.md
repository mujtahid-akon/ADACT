# AWorDS
For both macOS & Linux

***NOTE: `Match7.java` isn't working properly on macOS.***

## Installation

php 5.6.1+ is required.

#### Edit Config.php
Edit the `Config.php` located at the root of the project,
otherwise mail client, mysql server may not work. At least
the following constants are needed to be edited:
```php
Interface Config{
    const MYSQL_USER = 'root';
    const MYSQL_PASS = 'root';
    const MYSQL_DB   = 'awords';
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

#### Disable output buffering
Set `output_buffering = off` in `php.ini` file.

#### Enable Cron Job for running pending project(s)
```bash
bash ./scripts/add_to_crontab.sh
```

#### Run server

```bash
php -S 127.0.0.1:8080 ./router.php
```
(Also works with Apache2 when `htaccess` and `modrewrite` enabled)

#### Enable directory writing for /Projects
(This process is only applied to Linux distributions with Apache Server)

[Help Link](https://stackoverflow.com/a/16373988/4147849)
1. Check which `user` is running:
    ```bash
    ps aux
    ```
2. Add that user (assuming `www-data`) to the group:
    ```bash
    sudo chgrp -R  www-data ./ && sudo chmod -R g+w ./
    ```

## Contributing
For the sake of convenience, two scripts have been added in the `scripts` folder.
- `deploy.sh` : Run this before committing as it'll switch the configuration file
  (ie. `Config.php`) to the factory mode, as well as take care of DB.
  It takes one argument (sql version number) as it backs up the new sql file
  automatically.
- `revert.sh` : Revert the configuration file to the one that you were using previously (if you were)

## Not Implemented
- Edit last project
- Fork this project
- Change info
- Delete account
- Homepage

