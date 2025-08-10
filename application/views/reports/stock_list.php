name,volume,unit,lotnr,eol,volumes,btw_buy,in_price,in_price_avg
<?php foreach($stock_list as $stock): ?>
"<?php echo $stock['name']; ?>", <?php echo $stock['volume']; ?>, <?php echo $stock['unit_buy']; ?>, <?php echo $stock['lotnr']; ?>, <?php echo $stock['eol']; ?>, <?php echo $stock['concat_volume']; ?>, <?php echo $stock['btw_buy'] ?>, <?php echo $stock['in_price'] ?>, <?php echo $stock['avg_in_price'] ?>

<?php endforeach; ?>