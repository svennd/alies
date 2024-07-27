<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url('products'); ?>">Stock</a> / <?php echo $this->lang->line('move'); ?>
			</div>
			<div class="card-body">

				<form action="<?php echo base_url('stock/move_verification'); ?>" method="post" autocomplete="off" id="move_form">
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Product</th>
							<th>Info</th>
							<th><?php echo $this->lang->line('move'); ?></th>
						</tr>
					</thead>
				<?php foreach($selection as $stock_id => $p): ?>
				<?php list($stock, $volume) = $p; ?>
					<tr>
						<td><a href="<?php echo base_url('products/profile/' . $stock['products']['id']); ?>" target="_blank"><?php echo $stock['products']['name']; ?></a></td>
						<td>
							<small>
								<?php echo $stock['lotnr']; ?><br/>
								<?php echo $stock['eol']; ?>
							</small>
						</td>
						<td>
							<div class="input-group input-group-sm">
								<input type="text" class="form-control" name="move_volume[<?php echo $stock['id'] ?>]" required value="<?php echo $volume; ?>">
								<div class="input-group-append">
									<span class="input-group-text"><?php echo $stock['products']['unit_sell'] ?></span>
									<span class="input-group-text">/ <?php echo $stock['volume']; ?></span>
								</div>
							</div>
							<input type="hidden" class="form-control" name="max_move[<?php echo $stock['id'] ?>]" value="<?php echo $stock['volume']; ?>">
							<input type="hidden" class="form-control" name="move_prod[<?php echo $stock['id'] ?>]" value="<?php echo $stock['products']['id']; ?>">
						</td>
					</tr>
				<?php endforeach; ?>
				</table>
				
				<!-- location -->
				<div class="row pb-2">
					<div class="col">
					<?php echo $this->lang->line('from_location'); ?>: 				
						<select name="from" class="form-control" disabled>
							<?php foreach($stocks as $stock): if($stock['id'] != $from_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
						<input type="hidden" name="from" value="<?php echo $from_location; ?>">
					</div>
					<div class="col">		
					<?php echo $this->lang->line('to_location'); ?>: 	
						<select name="notupe" class="form-control" disabled>
							<?php foreach($stocks as $stock): if($stock['id'] != $user_location) { continue; } ?>
								<option value="<?php echo $stock['id']; ?>"><?php echo $stock['name']; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
				</div>

				<!-- just to verify -->
				Responsible :
				<input type="text" class="form-control" value="<?php echo $user->first_name; ?>" disabled>

				<!-- buttons -->
				<div class="text-right pt-3">
					<a href="<?php echo base_url('stock/move'); ?>" class="btn btn-sm btn-outline-danger ml-3">Cancel</a>
					<button type="submit" name="submit" value="quantities" class="btn btn-outline-success">Move</button>
				</div>
				</form>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#move_stock").addClass('active');
});
</script>