# medilab integration

Suggestions for crons : 
```
php index.php lab cron_medilab
```

possible to add multiple for quick followup : 
```
# no_redirect, 1 day scan (every 10 min for example)
php index.php lab cron_medilab -1 1

# no_redirect, 30 day scan
php index.php lab cron_medilab -1 30
```

By default 14 days are scanned.
