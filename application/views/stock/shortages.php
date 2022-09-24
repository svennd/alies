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
			  <li class="nav-item" role="presentation"><a class="nav-link active" id="info-tab" data-toggle="tab" href="#global" role="tab" aria-controls="info" aria-selected="true"><?php echo $this->lang->line('shortage'); ?> (<?php echo $this->lang->line('local'); ?>)</a></li>
			  <li class="nav-item" role="presentation"><a class="nav-link" id="local-tab" data-toggle="tab" href="#local" role="tab" aria-controls="stocktabs" aria-selected="false"><?php echo $this->lang->line('shortage'); ?> (<?php echo $this->lang->line('global'); ?>)</a></li>
			</ul>
			</div>
            <div class="card-body">
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane fade active show" id="global" role="tabpanel" aria-labelledby="global-tab">
				<br/>
				<?php if ($global_stock): ?>
				
					<table class="table" id="dataTable">
					<thead>
					<tr>
						<th>Product</th>
						<th>Limit</th>
						<th>Stock (global)</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($global_stock as $product): ?>
					<tr>
						<td>
							<?php if ($this->ion_auth->in_group("admin")): ?>
							<a href="<?php echo base_url(); ?>products/product/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
							<?php else: ?>
							<a href="<?php echo base_url(); ?>products/profile/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a>
							<?php endif; ?>
						</td>
							
						<td><?php echo $product['limit_stock']; ?> <?php echo $product['unit_sell']; ?></td>
						<td><?php echo $product['in_stock']; ?> <?php echo $product['unit_sell']; ?></td>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
				<?php else: ?>
					<?php echo $this->lang->line('no_shortage'); ?>
				<?php endif; ?>
				</div>
				<div class="tab-pane fade" id="local" role="tabpanel" aria-labelledby="local-tab">
					<br/>
					<?php if ($local_stock): ?>
					
						<table class="table" id="dataTable2">
						<thead>
						<tr>
							<th>Product</th>
							<th><?php echo $this->lang->line('location'); ?></th>
							<th>Limit</th>
							<th>Stock (<?php echo $this->lang->line('local'); ?>)</th>
							<th>Stock (<?php echo $this->lang->line('global'); ?>)</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($local_stock as $product): ?>
						<tr>
							<td><?php echo $product['name']; ?></td>
							<td><?php echo $loc[$product['location']]; ?></td>
							<td><?php echo $product['required_volume']; ?> <?php echo $product['unit_sell']; ?></td>
							<td><?php echo (is_null($product['available_volume'])) ? "0" : $product['available_volume']; ?> <?php echo $product['unit_sell']; ?></td>
							<td><a href="<?php echo base_url() . 'stock/stock_detail/' . $product['product_detail'];?>"><?php echo $product['all_volume']; ?> <?php echo $product['unit_sell']; ?></a></td>
						</tr>
						<?php endforeach; ?>
						</tbody>
						</table>
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
  