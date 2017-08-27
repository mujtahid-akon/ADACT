# AWorDS
For both macOS & Linux

***NOTE: `Match7.java` isn't working properly on macOS.***

## Instructions

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

#### Run server

```bash
php -S 127.0.0.1:8080 router.php
```
(Also works with Apache2 when `htaccess` and `modrewrite` enabled)

#### Enable directory writing for /Projects (Linux Only)

[Help Link](https://stackoverflow.com/a/16373988/4147849)
1. Check which `user` is running:
    ```bash
    ps aux
    ```
2. Add that user (assuming `www-data`) to the group:
    ```bash
    sudo chgrp -R  www-data ./ && sudo chmod -R g+w ./
    ```

## Not Implemented
- Edit last project
- Fork this project
- Change info
- Delete account
- Homepage

