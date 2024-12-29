<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div>
					<a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / 
					<a href="<?php echo base_url('wholesale/delivery_overview'); ?>"><?php echo $this->lang->line('delivery'); ?></a>
				</div>
			</div>
            <div class="card-body">
			<form action="<?php echo base_url('wholesale/delivery_overview'); ?>" method="post" autocomplete="off" class="form-inline">
				<div class="form-group mb-2 mr-3">
				<label for="search_from" class="sr-only">search_from</label>
				<input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ? $search_from : ''; ?>" id="search_from">
				</div>
				<div class="form-group mb-2">
				<span class="fa-stack" style="vertical-align: top;">
					<i class="far fa-square fa-stack-2x"></i>
					<i class="fas fa-arrow-right fa-stack-1x"></i>
				</span>
				</div>
				<div class="form-group mb-2 mx-3">
				<label for="search_to" class="sr-only">search_to</label>
				<input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ? $search_to : ''; ?>" id="search_to">
				</div>
				<button type="submit" name="submit" value="usage" class="btn btn-success mb-2"><?php echo $this->lang->line('search_range'); ?></button>
			</form>

			<?php if($deliveries): ?>
						<table class="table table-sm" id="deliveries">
						<thead>
						<tr>
							<th>Delivery date</th>
							<th>Products</th>
							<th>Number of packages</th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ($deliveries as $d):?>
						<tr>
							<td data-sort="<?php echo strtotime($d['delivery_date']); ?>"><a href="<?php echo base_url("wholesale/delivery/" . $d['delivery_date']); ?>"><?php echo user_format_date($d['delivery_date'], $user->user_date); ?></a></td>
							<td><?php echo $d['products']; ?></td>
							<td><?php echo $d['number']; ?></td>
						</tr>
						<?php endforeach; ?>
						</tbody>
						</table>
					<?php endif; ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#deliveries").DataTable({
		scrollY:        "65vh",
		deferRender:    true,
		scroller:       true,
		"order": [[ 0, "desc" ]]
	});
	$("#admin").addClass('active');
});
</script>
