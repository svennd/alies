<div class="row">
      <div class="col-lg-8 mb-4">
      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url(); ?>reports">Reports</a> / Usage</div>

				<?php if($usage): ?>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url(); ?>reports/products_csv/<?php echo ($search_from) ? $search_from : ''; ?>/<?php echo ($search_to) ? $search_to : ''; ?>" class="btn btn-outline-info btn-sm"><i class="fas fa-file-export"></i> Export to CSV</a>
				</div>
				<?php endif; ?>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>reports/products/" method="post" autocomplete="off" class="form-inline">

				  <div class="form-group mb-2 mr-3">
					<label for="staticEmail2" class="sr-only">search_from</label>
					<input type="date" name="search_from" class="form-control <?php echo ($search_from) ? 'is-valid' : ''; ?>" value="<?php echo ($search_from) ? $search_from : ''; ?>" id="search_from">
				  </div>
				  <div class="form-group mb-2">
					<span class="fa-stack" style="vertical-align: top;">
					  <i class="far fa-square fa-stack-2x"></i>
					  <i class="fas fa-arrow-right fa-stack-1x"></i>
					</span>
				  </div>
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">search_to</label>
					<input type="date" name="search_to" class="form-control <?php echo ($search_to) ? 'is-valid' : ''; ?>" value="<?php echo ($search_to) ? $search_to : ''; ?>" id="search_to">
				  </div>
				  <button type="submit" name="submit" value="usage" class="btn btn-success mb-2">Search range</button>
				</form>

				<?php if($usage): ?>
				<br>
				<?php ?>
				<table class="table">
					<tr>
						<th>Product</th>
						<th>Sold Volume</th>
						<th>Inprice</th>
						<th>Net Income</th>
					</tr>
					<?php foreach ($usage as $us): ?>
					<tr>
						<td rowspan="<?php echo count($us['in_price']) ?>"><?php echo $us['product']['name']; ?></td>
						<?php for($i = 0; $i < count($us['in_price']); $i++): ?>
						<?php echo ($i != 0) ? '<tr>' : ''; ?>
							<td><?php echo $us['volume'][$i]; ?> <?php echo $us['product']['unit_sell']; ?></td>
							<td><?php echo $us['in_price'][$i]; ?> &euro;</td>
							<td><?php echo $us['price_net'][$i]; ?> &euro;</td>
						</tr>
						<?php endfor; ?>
					<?php endforeach; ?>
					</table>
				<?php endif; ?>
                </div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#reportsmgm").addClass('active');
	$("#rep").show();
	$("#products_report").addClass('active');
});
</script>
