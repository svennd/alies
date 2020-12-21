# Introduction to Alies
[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fsvennd%2Falies.svg?type=shield)](https://app.fossa.com/projects/git%2Bgithub.com%2Fsvennd%2Falies?ref=badge_shield)

The open source veterinarian information management system for veterinary practices focussed on small pets (Dog, Cat, Horse, Bird, ...). Currently early in development, but useable :) 

## Install
This 
Requirements : 
- PHP 7+
- MySQL 5.7+ / MariaDB

1) Download the latest release
2) Run INSTALL.sql on your database 
3) Configure application/config/config.php (mainly : base_url) and application/config/database.php (hostname, username, password, database)
4) login at base_url with temporary info : (account) john@doe.no and (password) admin

## Linux : Debian specific
Install required packages :
```sh
apt-get install git mysql-server nginx php-fpm git php-gd php-mbstring php7.2-xml
```
Set the permissions to writable :
```sh
chmod 777 assets/public assets/barcode data data/stored
```
### Linux : Centos specific
Selinux context;
```sh
semanage fcontext -a -t httpd_sys_rw_content_t 'assets/barcode'
semanage fcontext -a -t httpd_sys_rw_content_t 'assets/public'
semanage fcontext -a -t httpd_sys_rw_content_t 'data'
semanage fcontext -a -t httpd_sys_rw_content_t 'data/stored'

restorecon -v 'assets/barcode'
restorecon -v 'assets/public'
restorecon -v 'data'
restorecon -v 'data/stored'
```
## License
[MIT](https://github.com/svennd/alies/blob/master/license.md)
(Might still change in the future when dependencies require stricter licenses)


[![FOSSA Status](https://app.fossa.com/api/projects/git%2Bgithub.com%2Fsvennd%2Falies.svg?type=large)](https://app.fossa.com/projects/git%2Bgithub.com%2Fsvennd%2Falies?ref=badge_large)

## Bugs, Feature requests
The project is at an early stage so bugs & errors are expected, please create a [new issue](https://github.com/svennd/alies/issues/new) whenever you have feedback; Thanks!

## Support
This project is done in my free time, feel free to buy me a [dr.pepper](https://www.buymeacoffee.com/SvennD) for my effords!