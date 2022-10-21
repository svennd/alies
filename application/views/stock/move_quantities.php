<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url(); ?>stock">Stock</a> / Move stock / Quantities</div>
				<div class="dropdown no-arrow">
					<?php foreach($locations as $location): ?>
						<?php if ($location['id'] != $new_location) { continue; } ?>
						<a href="#" class="btn btn-outline-success btn-sm"><i class="fas fa-shipping-fast"></i> <?php echo $location['name']; ?></a>
					<?php endforeach; ?>
				</div>
			</div>
			<div class="card-body">
				<?php if(count($warnings) > 0): ?>
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					<strong>Holy guacamole!</strong> We have some issue : 
					<ul>
					  <?php foreach($warnings as $w): ?>
						<li><?php echo $w; ?></li> 
					  <?php	endforeach; ?>
					</ul>
					  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					  </button>
					<a href="<?php echo base_url(); ?>stock" class="btn btn-outline-success"><i class="fas fa-undo"></i> retry</a>
				</div>
				<?php else: ?>
				<form action="<?php echo base_url(); ?>stock/move_stock" method="post" autocomplete="off">
				<?php foreach ($move_list as $barcode => $product): ?>
				<div class="form-row">
					<div class="form-group col-md-4">
						<label for="product<?php echo $barcode; ?>">Product</label>
						<input type="text" readonly class="form-control-plaintext" id="product" value="<?php echo $product['name']; ?>">
					</div>
					<div class="form-group col-md-2">
						<label for="eol<?php echo $barcode; ?>">eol</label>
						<input type="text" readonly class="form-control-plaintext" id="eol" value="<?php echo $product['eol']; ?>">
					</div>
					<div class="form-group col-md-2">
						<label for="lotnr<?php echo $barcode; ?>">lotnr</label>
						<input type="text" readonly class="form-control-plaintext" id="lotnr" value="<?php echo $product['lotnr']; ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="move_volume<?php echo $barcode; ?>">move volume</label>
						<div class="input-group">
						  <input type="text" class="form-control" id="move_volume<?php echo $barcode; ?>" name="move_volume[<?php echo $barcode; ?>]" required>
						  <div class="input-group-append">
							<span class="input-group-text"><?php echo $product['sell_unit']; ?></span>
							<span class="input-group-text">/ <?php echo $product['volume']; ?></span>
						  </div>
						  
						</div>
					</div>
				</div>
				<?php endforeach; ?>
				<input type="hidden" name="location" value="<?php echo $new_location; ?>"/>
				<button type="submit" name="submit" value="quantities" class="btn btn-primary">Submit</button>
				</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#prd").show();
	$("#products").addClass('active');
	$("#move_stock").addClass('active');
});
</script>