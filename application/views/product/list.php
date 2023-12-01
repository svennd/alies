	<p>
		    <div class="card shadow mb-4">

			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url(); ?>products">Products</a> / List</div>
				<?php if ($this->ion_auth->in_group("admin")): ?>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url(); ?>products/new" class="btn btn-outline-success btn-sm"><i class="fas fa-fw fa-plus"></i> new product</a>
				</div>
				<?php endif; ?>
			</div>

            <div class="card-body">
			<p>Type :
				<?php foreach ($types as $type): ?>
					<a href="<?php echo base_url(); ?>products/product_list/<?php echo $type['id'] ?>" class="btn btn-outline-primary"><?php echo $type['name']; ?></a>
				<?php endforeach; ?>
				<a href="<?php echo base_url(); ?>products/product_list/other" class="btn btn-outline-primary">Other</a>
			</p>
			<hr/>
			<br/>
			<?php if ($products): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Name</th>
					<th>Type</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product): ?>
				<tr>
					<td>
						<a href="<?php echo base_url('products/profile/' . $product['id']); ?>"><?php echo $product['name']; ?></a>
						<?php if ($product['name'] != $product['short_name']) : ?>
						<br/>
						<small><?php echo $product['short_name']; ?></small>
						<?php endif; ?>
					</td>
					<td><?php echo (isset($product['type']['name'])) ? $product['type']['name'] : 'Other'; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php endif; ?>
                </div>
		</div>
	</p>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#products").addClass('active');
});
</script>
