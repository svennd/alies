<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('wholesale'); ?>
			</div>
			<div class="card-body">
				<?php if ($products): ?>
				<table class="table table-sm" id="dataTable">
					<thead>
						<tr>
							<th>Description</th>
							<th>Intern</th>
							<th>Bruto</th>
							<th>last update</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($products as $p): ?>
						<tr>
							<td><a href="<?php echo base_url('wholesale/get_history/'. $p['id']); ?>"><?php echo (strlen($p['description']) > 20) ? substr($p['description'], 0, 20) . '...' : $p['description'];; ?></a></td>
							<td><?php echo (isset($p['product'])) ? "<a href='" . base_url('products/profile/' . $p['product']['id']) . "'>". $p['product']['name'] ."</a>" : ""; ?></td>
							<td><?php echo $p['bruto']; ?></td>
							<td data-sort="<?php echo strtotime($p['updated_at']); ?>"><?php echo (is_null($p['updated_at'])) ? '-' : date_format(date_create($p['updated_at']), $user->user_date); ?></td>
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