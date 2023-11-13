<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>stock">Stock</a> / <?php echo $this->lang->line('writeoff'); ?>
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
				<div class="row">
					<div class="col-md-4">
					<table class="table">
						<tr>
							<td><?php echo $this->lang->line('product'); ?></td>
							<td><?php echo $product['products']['name']; ?></td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('eol'); ?></td>
							<td><?php echo $product['eol']; ?></td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('lotnr'); ?></td>
							<td><?php echo $product['lotnr']; ?></td>
						</tr>
						<tr>
							<td><?php echo $this->lang->line('location'); ?></td>
							<td><?php echo $product['stock_locations']['name']; ?></td>
						</tr>
					</table>
					</div>
					<div class="col-md-6" style="border:1px solid red; padding:5px; border-radius:9px;">
						<form action="<?php echo base_url('stock/write_off/' . $product['id'] ); ?>" method="post" autocomplete="off">
						<div class="form-group row">
							<label for="volume" class="col-sm-2 col-form-label"><?php echo $this->lang->line('volume'); ?></label>
							<div class="col-sm-10">
								<div class="input-group">
								<input type="number" class="form-control" name="volume" step="0.01" min="0" max="<?php echo $product['volume']; ?>" required>
								<div class="input-group-append">
									<span class="input-group-text" id="basic-addon2">/ <?php echo $product['volume']; ?></span>
									<span class="input-group-text" id="basic-addon2"><?php echo $product['products']['unit_sell']; ?></span>
								</div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<label for="reason" class="col-sm-2 col-form-label"><?php echo $this->lang->line('reason'); ?></label>
							<div class="col-sm-10">
							<input type="text" class="form-control" name="reason" placeholder="" required minlength="3">
							<small id="birth_info" class="form-text text-muted ml-2">Please fill in a valid reason.</small>
							</div>
						</div>
						<input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
						<button type="submit" name="submit" value="write_off_q" class="btn btn-danger float-right"><i class="fa-solid fa-triangle-exclamation fa-beat"></i> <?php echo $this->lang->line('writeoff'); ?></button>
						<a href="<?php echo base_url('products/profile/' . $product['products']['id']); ?>" class="btn btn-success float-right mr-3"><i class="fa-solid fa-arrow-left"></i> <?php echo $this->lang->line('cancel'); ?></a>
						</form>
					</div>
				</div>
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
  