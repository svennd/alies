<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / Write off
			</div>
            <div class="card-body">
			<?php if(!$product): ?>
				<div class="alert alert-warning alert-dismissible fade show" role="alert">
					<strong>Holy guacamole!</strong><br/> 
					We haven't found that barcode;  <a href="<?php echo base_url(); ?>stock/write_off">Return</a>

					  <button type="button" class="close" data-dismiss="alert" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					  </button>
				</div>
			<?php else : ?>
				<table class="table">
					<tr>
						<td>Name</td>
						<td><?php echo $product['products']['name']; ?></td>
					</tr>
					<tr>
						<td>eol</td>
						<td><?php echo $product['eol']; ?></td>
					</tr>
					<tr>
						<td>lotnr</td>
						<td><?php echo $product['lotnr']; ?></td>
					</tr>
					<tr>
						<td>Barcode</td>
						<td><?php echo $product['barcode']; ?></td>
					</tr>
					<tr>
						<td>Location</td>
						<td>
						<?php
							foreach ($locations as $l)
							{
								echo ($l['id'] == $product['location']) ? $l['name'] : "";
							}
						?>
						</td>
					</tr>
				</table>
				<form action="<?php echo base_url(); ?>stock/write_off" method="post" autocomplete="off">
				<div class="form-group">
					<label for="barcodes">Volume :</label>
					<div class="input-group mb-3">
					  <input type="text" class="form-control" name="volume" placeholder="">
					  <div class="input-group-append">
						<span class="input-group-text" id="basic-addon2">/ <?php echo $product['volume']; ?></span>
						<span class="input-group-text" id="basic-addon2"><?php echo $product['products']['unit_sell']; ?></span>
					  </div>
					</div>
				</div>
					<input type="hidden" name="product_id" value="<?php echo $product['products']['id']; ?>">
					<input type="hidden" name="location" value="<?php echo $product['location']; ?>">
					<input type="hidden" name="barcode" value="<?php echo $product['barcode']; ?>">
				  <button type="submit" name="submit" value="write_off_q" class="btn btn-primary">Submit</button>
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
	$("#stock").addClass('active');
});
</script>
  