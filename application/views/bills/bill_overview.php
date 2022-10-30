<?php
$cd = new DateTime();
$cd->modify('-29 day');
$now = new DateTime();
$now->modify('+1 day');
?>
<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<?php echo $this->lang->line('title_invoice'); ?>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>invoice/index" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control" value="<?php echo (!empty($search_from)) ? $search_from : date_format($cd, 'Y-m-d'); ?>" min="<?php echo date_format($cd, 'Y-m-d'); ?>" id="search_from">
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
				  <button type="submit" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>

                </div>
		</div>
      <div class="card shadow mb-4">
			<div class="card-header">
				<?php echo $this->lang->line('invoices'); ?>
			</div>
            <div class="card-body">
			<?php if ($bills): ?>

				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th><i class="far fa-clock"></i> <?php echo $this->lang->line('date'); ?></th>
					<th><?php echo $this->lang->line('client'); ?></th>
					<th><?php echo $this->lang->line('state'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('amount'); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($bills as $bill): ?>
				<tr>
					<td data-sort="<?php echo strtotime($bill['created_at']) ?>">
						<?php echo user_format_date($bill['created_at'], $user->user_date); ?><br/>
						<small><?php echo timespan(strtotime($bill['created_at']), time(), 1); ?> Ago
					</td>
					<td><?php echo $bill['owner']['last_name']; ?></td>
					<td><?php echo get_bill_status($bill['status']); ?></td>
					<td><?php echo $bill['vet']['first_name']; ?></td>
					<td><?php echo (isset($bill['location']['name'])) ? $bill['location']['name']: 'unknown'; ?></td>

					<td><a href="<?php echo base_url(); ?>/invoice/get_bill/<?php echo $bill['id']; ?>"><?php echo $bill['amount']; ?> &euro;</a></td>
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
	$("#reportsmgm").addClass('active');
	$("#reportsmgm").show();
	$("#invoice_report").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50,
		"order": [[0, 'desc']],
  "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
