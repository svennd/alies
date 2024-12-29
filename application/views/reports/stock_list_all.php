name,volume,unit,unit_price,total_in_price,buy_volumes,volumes,in_price,btw_buy
<?php foreach($stock_list as $stock): ?>
"<?php echo $stock['name']; ?>","<?php echo $stock['volume']; ?>","<?php echo $stock['unit_buy']; ?>","<?php echo round($stock['total_in_price']/$stock['volume'], 2); ?>","<?php echo $stock['total_in_price']; ?>","<?php echo $stock['buy_volume']; ?>","<?php echo $stock['concat_volume']; ?>","<?php echo $stock['in_price']; ?>",<?php echo $stock['btw_buy'] ?>

<?php endforeach; ?>
