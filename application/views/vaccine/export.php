redo_date,last_name,first_name,street,nr,city,pets,last_bill,location,vet,product_name
<?php foreach($expiring_vacs as $v): ?>
<?php echo $v['redo_date']; ?>,<?php echo $v['last_name']; ?>,<?php echo $v['first_name']; ?>,<?php echo $v['owner_street']; ?>,<?php echo $v['owner_nr']; ?>,<?php echo $v['owner_city']; ?>,"<?php echo $v['pet_name']; ?>",<?php echo $v['last_bill']; ?>,<?php echo $v['location']; ?>,<?php echo $v['vet_name']; ?>,"<?php echo $v['product_name']; ?>"
<?php endforeach; ?>