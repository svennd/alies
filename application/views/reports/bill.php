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
				<a href="<?php echo base_url(); ?>reports">Reports</a> / Invoices / Range
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
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div>Invoices</div>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url(); ?>export/facturen/<?php echo $search_from; ?>/<?php echo $search_to; ?>" class="btn btn-outline-info btn-sm" download><i class="fas fa-file-export"></i> xml export</a>
				</div>
			</div>
            <div class="card-body">

			<?php if ($bills): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>#invoice</th>
					<th>amount</th>
					<th>client</th>
					<th>created</th>
					<th>location</th>
					<th>vet</th>
					<th>status</th>
					<th>updated</th>
					<th>edit</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($bills as $bill): ?>
				<tr>
					<td><a href="<?php echo base_url(); ?>/invoice/get_bill/<?php echo $bill['id']; ?>">#<?php echo get_bill_id($bill['id'], $bill['created_at']); ?></a></td>
					<td><?php echo $bill['amount']; ?> &euro;</td>
					<td>
						<?php echo ($bill['owner']['btw_nr']) ? "<i class='far fa-building'></i>" : "<i class='fas fa-user'></i>"; ?>
						<a href="<?php echo base_url('owners/detail/' . $bill['owner']['id']); ?>" class="<?php echo ($bill['owner']['debts']) ? "text-danger" : (($bill['owner']['low_budget']) ? "text-warning" : ""); ?>">
						<?php echo $bill['owner']['last_name']; ?>
						</a>
					</td>
					<td><?php echo user_format_date($bill['created_at'], $user->user_date); ?></td>
					<td><?php echo (isset($bill['location']['name'])) ? $bill['location']['name']: 'unknown'; ?></td>
					<td><?php echo $bill['vet']['first_name']; ?></td>
					<td><?php echo $state[$bill['status']]; ?></td>
					<td><?php echo (is_null($bill['updated_at'])) ? '-' : timespan(strtotime($bill['updated_at']), time(), 1) . ' ago'; ?></td>
					<td><a href='<?php echo base_url('admin_invoice/edit_bill/' . $bill['id']); ?>' class="btn btn-outline-danger btn-sm">edit</a></td>

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
	$("#reportsmgm").addClass('active');
	$("#rep").show();
	$("#invoice_report").addClass('active');

	$("#dataTable").DataTable({"pageLength": 50,
		"order" : [[ 0, "desc"]],
		"lengthMenu": [[50, 100, -1], [50, 100, "All"]]})
});

</script>
