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
	 		<div class="card-header border-bottom">
			<ul class="nav nav-tabs card-header-tabs" id="mynavtab" role="tablist">
				<li class="nav-item" role="presentation"><a class="nav-link" href="<?php echo base_url('limits/global'); ?>"><?php echo $this->lang->line('shortage'); ?> (<?php echo $this->lang->line('global'); ?>)</a></li>
			
				<?php foreach ($locations as $loc): ?>
					<li class="nav-item" role="presentation"><a class="nav-link <?php echo ($loc['id'] == $filter) ? 'active' : '';?>" href="<?php echo base_url('limits/local/' . $loc['id']); ?>"><?php echo $loc['name']; ?></a></li>
				<?php endforeach; ?>
			</ul>
			</div>
            <div class="card-body">
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane show active fade" id="local" role="tabpanel" aria-labelledby="local-tab">
					<br/>
					<?php if ($local_stock): ?>
						<table class="table" id="dataTable2">
						<thead>
						<tr>
							<th>Product</th>
							<th><?php echo $this->lang->line('local'); ?> stock / limit</th>
							<th><?php echo $this->lang->line('global'); ?> stock / limit</th>
							<th>use <?php echo $this->lang->line('local'); ?> / <?php echo $this->lang->line('global'); ?> <small>*</small></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($local_stock as $product): ?>
						<tr>
							<td><?php echo $product['name']; ?></td>
							<td>
								<span style="color:red;"><?php echo (is_null($product['available_volume'])) ? "0" : $product['available_volume']; ?> <?php echo $product['unit_sell']; ?></span> / 
								<?php echo $product['required_volume']; ?> <?php echo $product['unit_sell']; ?>
							</td>
							<td>
								<a href="<?php echo base_url() . 'stock/stock_detail/' . $product['product_detail'];?>">
								<span style="color:<?php echo (is_null($product['all_volume']) && $product['global_limit'] > $product['all_volume']) ? 'red': 'green'; ?>">
									<?php echo (is_null($product['all_volume'])) ? "0" : $product['all_volume']; ?> <?php echo $product['unit_sell']; ?></span>
								</a> / 
								<?php echo $product['global_limit']; ?> <?php echo $product['unit_sell']; ?>
							</td>
							<td>
								<?php echo (is_null($product['local_use'])) ? "0" : $product['local_use']; ?> <?php echo $product['unit_sell']; ?> / 
								<?php echo (is_null($product['global_use'])) ? "0" : $product['global_use']; ?> <?php echo $product['unit_sell']; ?>
							</td>
						</tr>
						<?php endforeach; ?>
						</tbody>
						</table>
						<i><small>*</small> local & global use over the last month.</i>
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
	$("#prd").show();
	$("#products").addClass('active');
	$("#stock").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	$("#dataTable2").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
  