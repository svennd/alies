name,volume,unit,lotnr,eol,volumes,created_at
<?php foreach($stock_list as $stock): ?>
"<?php echo $stock['name']; ?>", <?php echo $stock['volume']; ?>, <?php echo $stock['unit_buy']; ?>, <?php echo $stock['lotnr']; ?>, <?php echo $stock['eol']; ?>, <?php echo $stock['concat_volume']; ?>, <?php echo $stock['created_at']; ?>

<?php endforeach; ?>
