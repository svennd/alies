<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">

		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url('reports/products'); ?>">Reports</a> / Usage / <?php echo $prod_info['name']; ?></div>
			<div class="dropdown no-arrow">
				<a href="<?php echo base_url('reports/usage_csv/' . $prod_info['id'] . '/' . (($search_from) ? $search_from : '') . '/' . (($search_to) ? $search_to : '') ); ?>"class="btn btn-outline-info btn-sm"><i class="fas fa-file-export"></i> Export</a>
			</div>
		</div>
        <div class="card-body">
				<form action="<?php echo base_url('reports/usage/' . $prod_info['id']); ?>" method="post" autocomplete="off" class="form-inline">

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
				<table class="table" id="dataTable">
					<thead>
					<tr>
						<th>Date</th>
						<th>Volume</th>
						<th>Lotnr</th>
						<th>EOL</th>
						<th>in_price</th>
						<th>Pet</th>
						<th>Client</th>
						<th>Vet</th>
						<th>Location</th>
						<th>Consult</th>
					</tr>
					</thead>
					<tbody>
					<?php foreach ($usage as $us): ?>
					<tr>
						<td  data-sort="<?php echo strtotime($us['event_created_at']); ?>"><?php echo user_format_date($us['event_created_at'], $user->user_date); ?></td>
						<td><?php echo $us['volume'] . ' ' . $prod_info['unit_sell']; ?></td>
						<td><?php echo $us['lotnr']; ?></td>
						<td><?php echo $us['eol']; ?></td>
						<td><?php echo $us['in_price']; ?></td>
						<td><a href="<?php echo base_url('pets/fiche/' . $us['pet_id']); ?>"><?php echo $us['petname']; ?></a></td>
						<td><a href="<?php echo base_url('owners/detail/' . $us['id']); ?>"><?php echo $us['last_name']; ?></a></td>
						<td><?php echo $us['first_name']; ?></td>
						<td><?php echo $us['stockname']; ?></td>
						<td><a href="<?php echo base_url('events/event/' . $us['event_id']); ?>" class="btn btn-outline-info">Consult</a></td>
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
	$("#reportsmgm").addClass('active');
	$("#rep").show();
	$("#products_report").addClass('active');
	$("#dataTable").DataTable();
});
</script>
