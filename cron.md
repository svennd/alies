# medilab integration

Suggestions for crons : 
```
php index.php cron medilab
```

possible to add multiple for quick followup : 
```
# no_redirect, 1 day scan (every 10 min for example)
php index.php lab cron_medilab 0 1

# no_redirect, 30 day scan
php index.php lab cron_medilab 0 30
```

By default 14 days are scanned.



*/9 * * * * php /var/www/html/public/index.php cron medilab -1 $cron_key >> /var/www/html/public/cron.log
50 23 * * * php /var/www/html/public/index.php cron stock_clean $cron_key >> /var/www/html/public/stock_clean.log
