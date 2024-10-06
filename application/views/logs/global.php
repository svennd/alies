<?php
// hard coded for now
function enhance_log($msg, $event)
{
	if ($event == 'update_product')
	{
		// Extract product ID
		preg_match("/id\s*:\s*(\d+)/", $msg, $matches);
		$id = $matches[1];

		// Create link and data section
		echo "<a href='" . base_url('products/profile/' . $id) . "' target='_blank'>$id</a>";
		$data_part = preg_replace("/id\s*:\s*\d+\s+/", '', $msg);
		echo "&nbsp;<small><a href='#' class='toggle-details'><i class='fa-solid fa-magnifying-glass'></i></a>
			<span class='details' style='display:none;'><br/>
				". str_replace(',', ',<br/>', $data_part) ." 
			</span>
			</small>";
	}
	else if (in_array($event, array("logout", "unset_location", "login_success", "login_failed")))
	{
		echo str_replace('user : ', '', $msg);
	}
	else if (in_array($event, array("bill_pay_complete", "generate_bill", "bill_pay_incomplete")))
	{
		preg_match("/bill_id:\s*(\d+)/", $msg, $m);
		echo "<a href='" . base_url('invoice/get_bill/' . $m[1]) . "' target='_blank'>" . $m[1] . "</a>";
	}	
	else if (in_array($event, array("new_event")))
	{
		preg_match("/event_id:\s*(\d+)/", $msg, $m);
		echo "<a href='" . base_url('events/event/' . $m[1]) . "' target='_blank'>" . $m[1] . "</a>";
	}
	else if ($event == "update_client")
	{
		preg_match("/client\s+(.+)\s+\((\d+)\)/", $msg, $m);
		echo "<a href='" . base_url('owners/detail/' . $m[2]) . "' target='_blank'>" . $m[1] . "</a>";
	}
	else if ($event == "new_product")
	{
		preg_match("/product_name:\s*(\w+)\s+id\s*:\s*(\d+)/", $msg, $m);
		echo "<a href='" . base_url('products/profile/' . $m[2]) . "' target='_blank'>" . $m[1] . "</a>";
	}
	else
	{
		echo $msg;
	}

}
?>
<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header">
				<a href="<?php echo base_url('accounting/dashboard/'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('logbook'); ?>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url('logs/nlog') ?>" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control" value="<?php echo $search_from; ?>" id="search_from">
				</div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control" value="<?php echo $search_to; ?>" max="<?php echo date_format(new DateTime(), 'Y-m-d'); ?>" id="search_to">
				  </div>
				  <button type="submit" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
				</form>
			<?php if ($logs): ?>

				<table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th>Date</th>
					<th>Time</th>
					<th>Level</th>
					<th>Vet</th>
					<th>Event</th>
					<th>Message</th>
					<th>Location</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($logs as $log): ?>
				<tr>
					<td data-sort="<?php echo strtotime($log['created_at']); ?>"><?php echo user_format_date($log['created_at'], $user->user_date); ?></td>
					<td><?php echo user_format_date($log['created_at'], "H:i"); ?></td>
					<td><?php echo get_error_level($log['level'], 1); ?></td>
					<td><?php echo (isset($log['vet']['first_name'])) ? $log['vet']['first_name'] : ''; ?></td>
					<td><?php echo $log['event']; ?></td>
					<td><?php echo enhance_log($log['msg'], $log['event']); ?></td>
					<td><?php echo (isset($all_locations[$log['location']]['name'])) ? $all_locations[$log['location']]['name'] : ""; ?></td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No log messages
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
	$("#dataTable").DataTable({
			scrollY:        825,
			deferRender:    true,
			scroller:       true,
			"order": [[ 0, "desc" ]]
	});
	$('.toggle-details').on('click', function() {
        $(this).next('.details').toggle();
    });
});
</script>
