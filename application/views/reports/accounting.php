<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
	 		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><a href="<?php echo base_url(); ?>reports">Reports</a> / Accounting</div>

				<?php if($usage): ?>
				<div class="dropdown no-arrow">
					<a href="<?php echo base_url(); ?>reports/accounting/<?php echo ($search_from) ? $search_from : ''; ?>/<?php echo ($search_to) ? $search_to : ''; ?>/<?php echo ($booking_s) ? $booking_s : ''; ?>/csv" class="btn btn-outline-info btn-sm"><i class="fas fa-file-export"></i> Export to CSV</a>
				</div>
				<?php endif; ?>
			</div>
            <div class="card-body">
				<form action="<?php echo base_url(); ?>reports/accounting/" method="post" autocomplete="off" class="form-inline">

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
				  <div class="form-group mb-2 mx-3">
					<label for="staticEmail2" class="sr-only">booking</label>
					<select name="booking" class="form-control <?php echo ($booking_s) ? 'is-valid' : ''; ?>" id="type">
						<?php foreach($booking as $t): ?>
							<option value="<?php echo $t['id']; ?>" <?php echo ($booking_s == $t['id']) ? 'selected' : ''; ?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
						<?php endforeach; ?>
					</select>	
				  </div>
				  <button type="submit" name="submit" value="usage" class="btn btn-success mb-2">Search range</button>
				</form>

				<?php if($usage[0]): ?>
<div class="alert alert-secondary" role="alert">
beta : only products are included (procedures are missing from csv).
</div>
				<br>
				<table class="table" id="dataTable">
				<thead>
					<tr>
						<th>Product</th>
						<th>In price</th>
						<th>Netto sell</th>
						<th>Reduction</th>
						<th>client</th>
						<th>location</th>
						<th>factuur</th>
					</tr>
    </thead>
	<tbody>
				<?php foreach ($usage[0] as $us): ?>
					<tr>
						<td><?php echo $us['pname']; ?></td>
						<td><?php echo $us['in_price']; ?></td>
						<td><?php echo $us['net_price']; ?></td>
						<td><?php echo ($us['calc_net_price'] > 0) ? 'yes' : 'no'; ?></td>
						<td><?php echo $us['lname']; ?></td>
						<td><?php echo $us['name']; ?></td>
						<td>
							<?php if(isset($us['invoice_id'])): ?>
							<a href="<?php echo base_url('invoice/get_bill/' . $us['bill_id']) . '/1'; ?>"><?php echo get_invoice_id($us['invoice_id'], $us['invoice_date'], $this->conf['invoice_prefix']['value']); ?></a></td>
							<?php else: ?>
							<a href="<?php echo base_url('invoice/get_bill/' . $us['bill_id']); ?>"><?php echo $us['bill_id']; ?> (NB)</a></td>
							<?php endif; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
				<?php endif; ?>

				<?php if($usage[1]): ?>
<div class="alert alert-secondary" role="alert">
beta : only products are included (procedures are missing from csv).
</div>
				<br>
				<table class="table" id="">
					<tr>
						<th>Product</th>
						<th>In price</th>
						<th>Netto sell</th>
						<th>Reduction</th>
						<th>client</th>
						<th>event</th>
					</tr>
				<?php foreach ($usage[1] as $us): ?>
					<tr>
						<td><?php echo $us['name']; ?></td>
						<td><?php echo $us['price']; ?></td>
						<td><?php echo $us['net_price']; ?></td>
						<td><?php echo ($us['calc_net_price'] > 0) ? 'yes' : 'no'; ?></td>
						<td><?php echo $us['name']; ?></td>
						<td><?php echo $us['id']; ?></td>
					</tr>
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
	$("#dataTable").DataTable();
});
</script>
