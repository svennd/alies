<?php
$state = array(
				"OPEN",
				"UNPAID",
				"PARTIALLY",
				"PAID",
				"NON_COLLECTABLE",
				);
?>
<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>invoice">Invoices</a>
			</div>
            <div class="card-body">

			<?php if ($bills): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th># id</th>
					<th>Amount</th>
					<th>Client</th>
					<th>Status</th>
					<th><i class="fas fa-user-md"></i> Vet</th>
					<th><i class="fas fa-compass"></i> Location</th>
					<th><i class="far fa-clock"></i> Created</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($bills as $bill): ?>
				<tr>
					<td><a href="<?php echo base_url(); ?>/invoice/get_bill/<?php echo $bill['id']; ?>">#<?php echo $bill['id']; ?></a></td>
					<td><?php echo $bill['amount']; ?> &euro;</td>
					<td><?php echo $bill['owner']['last_name']; ?></td>
					<td><?php echo $state[$bill['status']]; ?></td>
					<td><?php echo $bill['vet']['first_name']; ?></td>
					<td><?php echo (isset($bill['location']['name'])) ? $bill['location']['name']: 'unknown'; ?></td>
					<td>
						<?php echo timespan(strtotime($bill['created_at']), time(), 1); ?> Ago <br/>
						<small>Update : <?php echo timespan(strtotime($bill['updated_at']), time(), 1); ?> Ago
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No bills in this view
			<?php endif; ?>
                </div>
		</div>

	</div>
      
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#invoice").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50,  
	"columnDefs": [
    { "type": "num", "targets": 0 }
	],
  "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
  