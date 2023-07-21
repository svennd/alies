<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<?php include 'header.php'; ?>
            <div class="card-body">
			<div class="tab-content" id="myTabContent">
				<div class="tab-pane fade active show" id="global" role="tabpanel" aria-labelledby="global-tab">
				<br/>
				<?php if ($in_backorder): ?>
					<table class="table" id="dataTable">
					<thead>
					<tr>
						<th>Product</th>
						<th>Vendor ID</th>
						<th>Vendor name</th>
						<?php if ($this->ion_auth->in_group("admin")): ?>
						<th>no order</th>
						<?php endif; ?>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($in_backorder as $product): ?>
					<tr>
						<td><a href="<?php echo base_url(); ?>products/profile/<?php echo $product['id']; ?>"><?php echo $product['name']; ?></a></td>
						<td><?php echo (isset($product['wholesale']) && is_array($product['wholesale']) ? $product['wholesale']['vendor_id'] : '---') ?></td>
						<td><?php echo (isset($product['wholesale']) && is_array($product['wholesale']) ? $product['wholesale']['description'] : '---') ?></td>
						<?php if ($this->ion_auth->in_group("admin")): ?>
						<td>
							<a href="<?php echo base_url('products/unset_backorder/' . $product['id']); ?>" class="btn btn-sm btn-outline-danger"><i class="fa-solid fa-ban"></i></a>
						</td>
						<?php endif; ?>
					</tr>
					<?php endforeach; ?>
					</tbody>
					</table>
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
});
</script>
  