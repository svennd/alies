<p>Welcome</p>

<?php if(!is_bool($update_to_version)) : ?>
<div class="alert alert-success" role="alert">Upgraded database schema to version <?php echo $update_to_version; ?></div>
<?php endif; ?>

<?php if ($current_location == "none"): ?>
Please select your location :<br/><br/>
<div class="row">
	<?php foreach($locations as $l) : ?>
	
  <div class="col-sm-3"><a class="btn btn-warning" href="<?php echo base_url() . '/welcome/change_location/' . $l['id']; ?>"><?php echo $l['name'] ?></a></div>
	<?php endforeach; ?>
</div>
<?php endif; ?>