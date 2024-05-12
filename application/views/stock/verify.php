<?php 
	function last_price(float $inprice, $last_net_price, $last_delivery): 
		if ($last_net_price)
		{
			return ($inprice < $last_net_price) ? $last_net_price : "<span class='text-danger'>" . $last_net_price . "</span>";
		}
		elseif ($last_delivery)
		{
			return ($inprice < $last_delivery) ? $last_delivery : "<span class='text-danger'>" . $last_delivery . "</span>";
		}
		else
		{
			return '-';
		}
	}
?>
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">		
			<div class="card-header">
				<a href="<?php echo base_url('stock'); ?>">Stock</a> / check
			</div>
			<div class="card-body">
			<form action="<?php echo base_url('stock/verify_stock'); ?>" method="post" autocomplete="off">

				<div class="form-group">
					<label for="delivery_slip"><?php echo $this->lang->line('delivery_date'); ?></label>
					<input type="date" name="regdate" class="form-control" id="date" value="<?php echo date('Y-m-d') ?>">
				</div>
				<div class="form-group">
					<label for="delivery_slip"><?php echo $this->lang->line('comment'); ?></label>
					<textarea class="form-control" name="delivery_slip" id="delivery_slip" rows="3"></textarea>
				</div>
			<hr>
			<?php if($products): ?>
			<table class="table table-sm">
				<tr>
					<td><?php echo $this->lang->line('name'); ?></td>
					<td><?php echo $this->lang->line('lotnr'); ?></td>
					<td><?php echo $this->lang->line('eol'); ?></td>
					<td><?php echo $this->lang->line('price_dayprice'); ?> (input)</td>
					<td>last <?php echo $this->lang->line('price_dayprice'); ?></td>
					<td><?php echo $this->lang->line('volume'); ?></td>
					<td><?php echo $this->lang->line('option'); ?></td>
				</tr>
			<?php foreach($products as $prod): ?>
				<tr>
					<td><a href="<?php echo base_url('products/profile/' . $prod['product_id']); ?>" target="_blank"><?php echo $prod['products']['name']; ?></a></td>
					<td><small><?php echo $prod['lotnr']; ?></small></td>
					<td><small><?php echo user_format_date($prod['eol'], $user->user_date); ?></small></td>
					<td><?php echo $prod['in_price']; ?> &euro;</td>
					<td><?php echo last_price($prod['in_price'], $pricing[$prod['product_id']]['last_net_buy'], $pricing[$prod['product_id']]['delivery'] ); ?> &euro;</td>
					<td><?php echo $prod['volume'] . ' ' . $prod['products']['unit_sell']; ?></td>
					<td>
						<a href="<?php echo base_url('stock/delete_stock/' . $prod['id']); ?>" class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></a>
						<?php if ($this->ion_auth->in_group("admin")): ?>
						<a href="<?php echo base_url('pricing/prod/' . $prod['id']); ?>" class="btn btn-sm btn-success ml-5"><i class="fa-solid fa-fw fa-euro-sign"></i></a>
						<?php endif; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</table>
			<?php endif; ?>
				<button type="submit" name="submit" value="1" class="btn btn-sm btn-primary"><i class="fas fa-shipping-fast"></i> <?php echo $this->lang->line('verify_stock'); ?></button>
			</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">

document.addEventListener("DOMContentLoaded", function(){

	$("#add_stock").addClass('active');
	
});
</script>
  