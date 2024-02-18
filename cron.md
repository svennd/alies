# crons
see `php index.php cli` for all options.


# suggested timing

```
# run daily : stock_clean, auto_death, prune, autoclose 
@daily php /var/www/html/public/index.php cli daily

# or if you want to specify 
# 0 0 * * * php /var/www/html/public/index.php cli daily

# run every 15 minutes
*/15 * * * * php /var/www/html/public/index.php cli medilab

```

_note : /var/www/html/public is the path here, your path most likely will be different_