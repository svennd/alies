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
			  <li class="nav-item" role="presentation"><a class="nav-link active" id="info-tab" data-toggle="tab" href="#global" role="tab" aria-controls="info" aria-selected="true"><?php echo $this->lang->line('shortage'); ?> (<?php echo $this->lang->line('global'); ?>)</a></li>
			  <li class="nav-item" role="presentation"><a class="nav-link" href="<?php echo base_url('limits/local/' . $this->user->current_location); ?>"><?php echo $this->lang->line('shortage'); ?> (<?php echo $this->lang->line('local'); ?>)</a></li>
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
				
			</div>

                </div>
		</div>

	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){

	$("#product_list").addClass('active');
	$("#dataTable").DataTable();
	$("#dataTable2").DataTable();
});
</script>
  