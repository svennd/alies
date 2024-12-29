id,first_name,last_name,street,nr,city,zip,last_bill
<?php foreach($clients as $client): ?>
<?php echo $client['id']; ?>,"<?php echo $client['first_name']; ?>","<?php echo $client['last_name']; ?>","<?php echo $client['street']; ?>","<?php echo $client['nr']; ?>","<?php echo $client['city']; ?>","<?php echo $client['zip']; ?>",<?php echo $client['last_bill'] ?>

<?php endforeach; ?>
