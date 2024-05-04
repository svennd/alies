<style>
.dropbox{
    border:2px dashed #d3d8e8;
}

.dropbox:hover {
    border:2px dashed #6984da;
	color:#6984da;
}

.drag-over{
    border:2px dashed #6984da;
	color:#6984da;
}

</style>
<div class="row">
	<div class="col-lg-10 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('wholesale'); ?>
			</div>
			<div class="card-body">
				<?php if ($products): ?>
				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th>Description</th>
							<th>Intern</th>
							<th>Bruto</th>
							<th>Diff</th>
							<th>Pricing</th>
							<th>last update</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($products as $p): ?>
							<?php 
							$calculated_prct = false;
							if ($p['last_bruto'] != NULL && $p['last_bruto'] != 0 && $p['bruto'] != 0 && $p['ignore_change'] != 1)
							{
								# calculate the different in %
								$calculated_prct = round((($p['bruto'] / $p['last_bruto']) * 100) - 100);
								$procent = ($calculated_prct > 0) ? 
									"<span style='color:tomato'>" . $calculated_prct . "%</span>"
									:
									(
										($calculated_prct == 0 ) ? 
										"" :
										"<span style='color:green'>" . $calculated_prct . "%</span>"
									)
									;
							}
							else { $procent = ""; }
							?>
						<tr>
							<td><a href="<?php echo base_url('wholesale/get_history/'. $p['id']); ?>"><?php echo $p['description']; ?></a></td>
							<td><?php echo (isset($p['product'])) ? "<a href='" . base_url('products/profile/' . $p['product']['id']) . "'>". $p['product']['name'] ."</a>" : ""; ?></td>
							<td><?php echo $p['bruto']; ?></td>
							<td data-sort="<?php echo ($calculated_prct) ? $calculated_prct : "0"; ?>">
								<?php echo $procent; ?>
								<?php if($calculated_prct): ?>
								<a href="<?php echo base_url('wholesale/accept/' . $p['id']); ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-rotate-right"></i></a>
								<a href="<?php echo base_url('wholesale/ignore/' . $p['id']); ?>" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-eye-slash fa-fw"></i></a>
								<?php endif; ?>
							</td>
							<td>
								<?php if(isset($p['product'])): ?>
								<a href="<?php echo base_url('pricing/prod/' . $p['product']['id']); ?>" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-fw fa-euro-sign"></i></a>
								<?php endif; ?>
								
							</td>
							<td><?php echo (is_null($p['updated_at'])) ? '-' : date_format(date_create($p['updated_at']), $user->user_date); ?></td>
						</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				<?php else: ?>
					No products in wholesale.
				<?php endif; ?>
			</div>
		</div>
	</div>

	<div class="col-lg-2 mb-4">
			<!--- upload pricelist -->
			<div class="card shadow mb-4">		
				<div class="card-header">
					Upload price list covertrus
				</div>
				<div class="card-body">
					<div class="dropbox d-flex align-items-center" id="upload_field">
						<p class="text-center w-100"><i class="fas fa-cloud-upload-alt fa-3x m-2"></i><br/>Click here or drag the files here.</p>
					</div>
					<input type="file" style="display:none" name="manual_file_upload" id="my_old_browser" multiple />
					
					<h1></h1>
					<form action="#" method="post" autocomplete="off">
						<input type="hidden" id="file_upload" name="file" value="" />
						<button type="submit" name="submit" value="1" class="btn btn-primary">Process data</button>
					</form>
				</div>
			</div>

			<!--- upload delivery -->
			<div class="card shadow mb-4">		
				<div class="card-header">
					Upload delivery covertrus
				</div>
				<div class="card-body">
					<div class="dropbox d-flex align-items-center" id="upload_field">
						<p class="text-center w-100"><i class="fas fa-cloud-upload-alt fa-3x m-2"></i><br/>Click here or drag the files here.</p>
					</div>
					<input type="file" style="display:none" name="manual_file_upload" id="my_old_browser" multiple />
					
					<h1></h1>
					<form action="#" method="post" autocomplete="off">
						<input type="hidden" id="file_upload" name="file" value="" />
						<button type="submit" name="submit" value="1" class="btn btn-primary">Process data</button>
					</form>
				</div>
			</div>

			<!--- deliveries -->
			<?php if ($deliveries): ?>
			<div class="card shadow mb-4">		
				<div class="card-header">
					Last 5 deliveries
				</div>
				<div class="card-body">
					<table class="table table-sm">
						<thead>
							<tr>
								<th>Delivery</th>
								<th>Products</th>
								<th>Packages</th>
							</tr>
						</thead>
						<tbody>
							<?php foreach($deliveries as $delivery): ?>
								<tr>
									<td><a href="<?php echo base_url('wholesale/delivery/' . $delivery['delivery_date']); ?>"><?php echo $delivery['delivery_date'] ?></a></td>
									<td><?php echo $delivery['products'] ?></td>
									<td><?php echo $delivery['number'] ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				</div>
			</div>
			<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable({
		responsive: 	true,
		scrollY:        '65vh',
		deferRender:    true,
		scroller:       true,
		"order": [[ 3, "desc" ]]
	});
	$("#admin").addClass('active');
});
</script>