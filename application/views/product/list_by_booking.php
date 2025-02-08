<div class="card shadow mb-4">

	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div><a href="<?php echo base_url(); ?>products"><?php echo $this->lang->line('products'); ?></a> / <?php echo $this->lang->line('list_products') ?> / <?php echo $booking['category'] ?> - <?php echo $booking['code'] ?></div>
		<?php if ($this->ion_auth->in_group("admin")): ?>
		<div class="dropdown no-arrow">
			<a href="<?php echo base_url('products/new'); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus"></i> new product</a>
		</div>
		<?php endif; ?>
	</div>

	<div class="card-body">
		<table class="table table-sm" id="dataTable">
		<thead>
		<tr>
			<th><?php echo $this->lang->line("name"); ?></th>
			<th><?php echo $this->lang->line("alternative_name"); ?></th>
			<th><?php echo $this->lang->line("type"); ?></th>
		</tr>
		</thead>
		<tbody>
			<?php if ($products): ?>
				<?php foreach ($products as $product): ?>
				<tr>
					<td><a href='<?php echo base_url('products/profile/' . $product['id']); ?>'><?php echo $product['name']; ?></a></td>
					<td><?php echo $product['wholesale_name']; ?></td>
					<td><span class="badge badge-success">prod<span></td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
			<?php if ($procedures): ?>
				<?php foreach ($procedures as $proc): ?>
				<tr>
					<td><?php echo $proc['name']; ?></td>
					<td>-</td>
					<td><span class="badge badge-primary">proc</span></td>
				</tr>
				<?php endforeach; ?>
			<?php endif; ?>
		</tbody>
		</table>
		</div>
</div>
	
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#products").addClass('active');
});
</script>
