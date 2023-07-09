product,in_price,netto,netto_ori,location,event_id
<?php foreach($usage[0] as $us): ?>
"<?php echo $us['pname']; ?>", <?php echo $us['in_price']; ?>, <?php echo $us['net_price']; ?>, <?php echo $us['calc_net_price']; ?>, <?php echo $us['name']; ?>, <?php echo $us['id']; ?>

<?php endforeach; ?>
