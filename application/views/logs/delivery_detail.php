<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>logs">Logs</a> / deliveries / detail
			</div>
            <div class="card-body">
				<table class="table">
					<tr>
						<td>Vet</td>
						<td><?php echo $delivery['vet']['first_name']; ?></td>
					</tr>
					<tr>
						<td>Note</td>
						<td><?php echo $delivery['note']; ?></td>
					</tr>
					<tr>
						<td>Regdate</td>
						<td><?php echo user_format_date($delivery['regdate'], $user->user_date); ?></td>
					</tr>
					<tr>
						<td>Location</td>
						<td><?php echo $delivery['location']['name']; ?></td>
					</tr>
				</table>
			<?php if ($products): ?>

				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Name</th>
					<th>Volume</th>
					<th>EOL</th>
					<th>in_price</th>
					<th>lotnr</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($products as $product): ?>
				<tr>
					<td><?php echo $product['product']['name']; ?></td>
					<td><?php echo $product['volume']; ?></td>
					<td><?php echo user_format_date($product['eol'], $user->user_date); ?></td>
					<td><?php echo $product['in_price']; ?></td>
					<td><?php echo $product['lotnr']; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No products found :s
			<?php endif; ?>
			</div>
		</div>

	</div>

</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#logs").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]],
	    "order": [[ 0, "desc" ]]});
});
</script>
