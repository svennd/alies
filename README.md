# Alies
The open source veterinarian information management system for veterinary practices.

# Install
Requirements : 
- PHP 7+
- MySQL 5.7+ / MariaDB

1) Download the latest release
2) Run INSTALL.sql on your database 
3) Configure application/config/config.php (mainly : base_url) and application/config/database.php (hostname, username, password, database)
4) login at base_url with temporary info : (account) john@doe.no and (password) admin

# Install Linux 
Requirements : 
1) apt-get install git mysql-server nginx php-fpm git php-gd php-mbstring php7.2-xml
2) chmod 777 assets/public assets/barcode data data/stored

# used tools : 
note : not all dependencies are up-to-date !
https://select2.org/
https://github.com/ttskch/select2-bootstrap4-theme/
https://www.svgminify.com/
https://www.pixilart.com/draw
https://www.devbridge.com/sourcery/components/jquery-autocomplete/#
https://github.com/lodev09/bootstrap-suggest : for messaging users
https://www.chartjs.org/samples/latest/scales/time/line.html
https://github.com/Foliotek/Croppie
