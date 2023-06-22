<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('products'); ?>">Products</a> / <?php echo $this->lang->line('move_stock'); ?> / <?php echo $this->lang->line('Quantity'); ?></div>
				<div class="dropdown no-arrow">
					<?php foreach($locations as $location): ?>
						<?php if ($location['id'] != $from_location) { continue; } ?>
						<a href="#" class="btn btn-outline-success btn-sm">
							<?php echo $location['name']; $from = $location['name']; // from ?>
							<i class="fa-solid fa-truck-arrow-right"></i>
							<?php echo $current_location; $to = $current_location;// to ?>
						</a>
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
						<label for="product<?php echo $barcode; ?>"><?php echo $this->lang->line('product'); ?></label>
						<input type="text" readonly class="form-control-plaintext" id="product<?php echo $barcode; ?>" value="<?php echo $product['name']; ?>">
					</div>
					<div class="form-group col-md-2">
						<label for="eol<?php echo $barcode; ?>"><?php echo $this->lang->line('eol'); ?></label>
						<input type="text" readonly class="form-control-plaintext" id="eol<?php echo $barcode; ?>" value="<?php echo $product['eol']; ?>">
					</div>
					<div class="form-group col-md-2">
						<label for="lotnr<?php echo $barcode; ?>"><?php echo $this->lang->line('lotnr'); ?></label>
						<input type="text" readonly class="form-control-plaintext" id="lotnr<?php echo $barcode; ?>" value="<?php echo $product['lotnr']; ?>">
					</div>
					<div class="form-group col-md-4">
						<label for="move_volume<?php echo $barcode; ?>"><?php echo $this->lang->line('volume'); ?></label>
						<div class="input-group">
						  <input type="text" class="form-control" id="move_volume<?php echo $barcode; ?>" name="move_volume[<?php echo $barcode; ?>]" required>
						  <div class="input-group-append">
							<span class="input-group-text"><?php echo $product['sell_unit']; ?></span>
							<span class="input-group-text">/ <?php echo $product['volume']; ?></span>
						  </div>
						</div>
					</div>
				</div>
				
				<hr>
				<div class="row">
					<div class="col-md-4">
						<!-- <?php var_dump($product['from_limit']); ?> -->
						<table class="table table-sm">
						<thead>
							<tr>
								<th>Huidige situatie</th>
								<th>Totaal (Lokale Limiet)</th>
							</tr>
						</thead>
						<tr>
							<td><?php echo $from; ?>:</td>
							<td><span id="A_o_<?php echo $barcode; ?>"><?php echo $product['from_stock'][0]; ?></span> <?php echo $product['sell_unit']; ?> (<?php echo $product['from_limit']['volume']; ?>)</td>
						<tr>
						<tr>
							<td><?php echo $to; ?>:</td>
							<td><span id="B_o_<?php echo $barcode; ?>"><?php echo $product['to_stock'][0]; ?></span> <?php echo $product['sell_unit']; ?> (<?php echo $product['to_limit']['volume']; ?>)</td>
						<tr>
						</table>
					</div>
					<div class="col-md-4">
						<table class="table table-sm">
						<thead>
							<tr>
								<th>Nieuwe situatie</th>
								<th>Totaal</th>
							</tr>
						</thead>
						<tr>
							<td><?php echo $from; ?>:</td>
							<td><span id="A<?php echo $barcode; ?>"><?php echo $product['from_stock'][0]; ?></span> <?php echo $product['sell_unit']; ?></td>
						<tr>
						<tr>
							<td><?php echo $to; ?>:</td>
							<td><span id="B<?php echo $barcode; ?>"><?php echo $product['to_stock'][0]; ?></span> <?php echo $product['sell_unit']; ?></td>
						<tr>
						</table>
					</div>
				</div>
				<?php endforeach; ?>
				<input type="hidden" name="new_location" value="<?php echo $new_location; ?>"/>
				<input type="hidden" name="from_location" value="<?php echo $from_location; ?>"/>
					<button type="submit" name="submit" value="quantities" class="btn btn-success"><?php echo $this->lang->line('move'); ?></button>
					<a href="<?php echo base_url('products'); ?>" class="btn btn-danger ml-3"><?php echo $this->lang->line('cancel'); ?></a>
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

	$('[id^="move_volume"]').on('input', function() {
		// Get the barcode value
		var barcode = $(this).attr('id').substr(11);
		
		// Get the corresponding element IDs for A and B
		var elementIDA = 'A' + barcode;
  		var elementIDB = 'B' + barcode;

		// Get the input value
		var inputValue = parseFloat($(this).val());

		if (Number.isFinite(inputValue)) {
			// Subtract the input value from the initial values
			var newValueA = parseFloat($('#A_o_' + barcode).text()) - inputValue;
			var newValueB = parseFloat($('#B_o_' + barcode).text()) + inputValue;

			// Update the corresponding span elements
			$('#' + elementIDA).text(newValueA);
			$('#' + elementIDB).text(newValueB);
		}
	});

});
</script>