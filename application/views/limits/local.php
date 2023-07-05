<?php
# map this at global level
# todo
foreach ($locations as $l)
{
	$loc[$l['id']] = $l['name'];
}
?>
<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<?php include 'header.php'; ?>
            <div class="card-body">
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane show active fade" id="local" role="tabpanel" aria-labelledby="local-tab">
					<br/>
					<?php if ($local_stock): ?>
						<table class="table" id="dataTable2">
						<thead>
						<tr>
							<th>Product</th>
							<th><?php echo $this->lang->line('local_stock'); ?> / <?php echo $this->lang->line('limit'); ?></th>
							<th><?php echo $this->lang->line('global_stock'); ?> / <?php echo $this->lang->line('limit'); ?></th>
							<th><?php echo $this->lang->line('local'); ?> / <?php echo $this->lang->line('global_use'); ?> <small>*</small></th>
							<th>Prio</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($local_stock as $product): ?>
						<?php 
							$prio = 1; 
							$local_use = (is_null($product['local_use'])) ? 0 : $product['local_use'];
							$global_use = (is_null($product['global_use'])) ? 0 : $product['global_use'];
							$local_available = (is_null($product['available_volume'])) ? "0" : $product['available_volume'];
							$global_available = (is_null($product['all_volume'])) ? "0" : $product['all_volume'];
							
							// determ prio
							if($local_use == 0) 
							{
								// no local use
								$prio -= 2;
							}
							elseif ($local_use <= $local_available)
							{
								// there is still enough to have same as last month
								$prio -= 1;
							}
							elseif ($local_use > $local_available)
							{
								// we won't have enough for end of month
								// $prio += 1;
								$laval = ($local_available <= 0) ? 1 : $local_available;
								$prio += $local_use/$laval;
							}

							if($global_use == 0)
							{
								// globally 0 use : probably just something required for tracking
								$prio -= 1;
							}
							elseif($global_use == $local_use)
							{
								// all the use is local, so shortages are more critical
								$prio += 1;
							}

							// there are too little available "globally" (so we can assume locals will be too low)
							if ($global_available < $product['global_limit'])
							{
								$prio -= 1;
							}
							
						?>

						<tr <?php echo ($prio > 1) ? 'class="table-warning"' : ''; ?>>
							<td><a href="<?php echo base_url('products/profile/' . $product['product_detail']); ?>"><?php echo $product['name']; ?></a></td>
							<td>
								<span style="color:red;"><?php echo $local_available; ?> <?php echo $product['unit_sell']; ?></span> / 
								<?php echo $product['required_volume']; ?> <?php echo $product['unit_sell']; ?>
							</td>
							<td>
								<a href="<?php echo base_url() . 'stock/stock_detail/' . $product['product_detail'];?>">
								<span style="color:<?php echo ($global_available >= 0 && $product['global_limit'] > $product['all_volume']) ? 'red': 'green'; ?>">
									<?php echo $global_available; ?> <?php echo $product['unit_sell']; ?></span>
								</a> / 
								<?php echo $product['global_limit']; ?> <?php echo $product['unit_sell']; ?>
							</td>
							<td>
								
								<?php if ($this->ion_auth->in_group("admin")): ?><a href="<?php echo base_url('reports/usage/' . $product['product_detail']); ?>"><?php endif; ?>
								<?php echo $local_use; ?> <?php echo $product['unit_sell']; ?> / 
								<?php echo $global_use; ?> <?php echo $product['unit_sell']; ?>
								<?php if ($this->ion_auth->in_group("admin")): ?></a><?php endif; ?>
							</td>
							<td>
								<?php echo round($prio, 2); ?>
							</td>
						</tr>
						<?php endforeach; ?>
						</tbody>
						</table>
						<i><small>*</small> <?php echo $this->lang->line('explain_month'); ?></i>
					<?php else: ?>
						<?php echo $this->lang->line('no_shortage'); ?>
					<?php endif; ?>
				</div>
			</div>

                </div>
		</div>

	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	
	$("#product_list").addClass('active');
	$("#dataTable2").DataTable({"order": [[ 4, "desc" ]]});
});
</script>
  