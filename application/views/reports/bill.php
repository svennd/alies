<?php
$state = array(
				"OPEN",
				"UNPAID",
				"PARTIALLY",
				"PAID",
				"NON_COLLECTABLE",
				);
$now = new DateTime();
$now->modify('+1 day');
$cd = new DateTime();
$cd->modify('-3 month');
?>
      <div class="card shadow mb-4">
			<div class="card-header">
				Report / Invoices / search
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>reports/bills/" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control" value="<?php echo (!empty($search_from)) ? $search_from : date_format($cd, 'Y-m-d'); ?>" id="search_from">
				  </div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control" value="<?php echo (!empty($search_to)) ? $search_to : date_format($now, 'Y-m-d'); ?>" max="<?php echo date_format($now, 'Y-m-d'); ?>" id="search_to">
				  </div>
				  <button type="submit" class="btn btn-success mb-2">Search range</button>
				</form>

                </div>
		</div>
		<div class="card shadow mb-4">
			<div class="card-header">
				Invoices
			</div>
            <div class="card-body">

			<?php if ($bills): ?>
				<a href="<?php echo base_url(); ?>export/facturen/<?php echo $search_from; ?>/<?php echo $search_to; ?>" class="btn <?php echo (count($bills) == $read_limit) ? 'btn-danger' : 'btn-info'; ?> mb-2" download><i class="fas fa-file-export"></i> xml export</a>
				<?php if (count($bills) == $read_limit) : ?>
					<div class="alert alert-danger" role="alert">You can only query <?php echo $read_limit; ?> at once !</div>
				<?php endif; ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>#invoice id</th>
					<th>vet</th>
					<th>location</th>
					<th>amount</th>
					<th>status</th>
					<th>updated</th>
					<th>created</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($bills as $bill): ?>
				<tr>
					<td><a href="<?php echo base_url(); ?>/invoice/get_bill/<?php echo $bill['id']; ?>">#<?php echo $bill['id']; ?></a></td>
					<td><?php echo $bill['vet']['first_name']; ?></td>
					<td><?php echo (isset($bill['location']['name'])) ? $bill['location']['name']: 'unknown'; ?></td>
					<td><?php echo $bill['amount']; ?> &euro;</td>
					<td><?php echo $state[$bill['status']]; ?></td>
					<td><?php echo (is_null($bill['updated_at'])) ? '-' : timespan(strtotime($bill['updated_at']), time(), 1) . ' ago'; ?></td>
					<td><?php echo user_format_date($bill['created_at'], $user->user_date); ?></td>
					<!-- <td><?php echo timespan(strtotime($bill['created_at']), time(), 1); ?> Ago</td> -->
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No bills in this view
			<?php endif; ?>
                </div>
		</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#reports").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50,
	"columnDefs": [
    { "type": "num", "targets": 0 }
	],
  "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
