<div class="row">
	<div class="col-lg-12 mb-4">
	  <div class="card shadow mb-4">
	  	<div class="card-header">
			<a href="<?php echo base_url('pricing/proc'); ?>">Procedures</a> / 
			Edit Procedure
		</div>
			<div class="card-body">
				<form method="POST" name="edit_procedure" id="edit_procedure" action="<?php echo base_url('pricing/proc'); ?>" class="confirm-new-procedure">
					<div class="form-group">
						<label for="name">Procedure name</label>
						<input type="text" class="form-control" id="name" name="name" value="<?php echo $proc['name']; ?>" maxlength="255">
					</div>
					<div class="form-group">
						<label for="price">Procedure price</label>
						<div class="input-group">
							<input type="number" step="0.01" min="0" max="1000"  class="form-control" id="price" name="price" value="<?php echo $proc['price']; ?>">
							<div class="input-group-append">
								<span class="input-group-text" id="basic-addon2">&euro;</span>
							</div>
						</div>
					</div>
					<div class="form-group">
						<label for="booking_code">Booking code</label>
						<select name="booking_code" class="form-control" id="booking_code">
							<?php foreach($booking as $t): ?>
								<option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $proc['booking_code']['id']) ? "selected='selected'":"";?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<input type="hidden" name="id" value="<?php echo $proc['id'] ?>">
					<input type="hidden" name="action" value="edit_proc"> <!-- required for swal2 -->
					<button type="button" id="btn_edit_proc" name="whatev" value="edit_proc" class="btn btn-primary mb-2">Edit Procedure</button>
				</form>
			</div>
		</div>
	</div>
	<?php if($this->ion_auth->in_group("admin")): ?>
	<div class="col-lg-8 mb-4">
	  <div class="card shadow mb-4">
	  	<div class="card-header">Statistics</div>
			<div class="card-body">
				<table class="table table-sm">
					<thead>
						<tr>
							<th>Period</th>
							<th>Total net income</th>
							<th>Total (unique)</th>
							<th>Total (sum)</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td>3 Months</td>
							<td><?php echo round($stat['total_net_price_3_months'], 2); ?> &euro;</td>
							<td><?php echo $stat['total_amount_3m']; ?></td>
							<td><?php echo $stat['total_count_3m']; ?></td>
						</tr>
					<tbody>
						<tr>
							<td>1 Year</td>
							<td><?php echo round($stat['total_net_price_1_year'], 2); ?> &euro;</td>
							<td><?php echo $stat['total_amount_1y']; ?></td>
							<td><?php echo $stat['total_count_1y']; ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
	<?php endif; ?>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#adminproc").addClass('active');

	$('form #btn_edit_proc').click(function(e) {
        // event.preventDefault(); // Prevent the default form submission
        console.log("atleast this far");
        let $form = $(this).closest('form');
                
        var name = $form.find('input[name="name"]').val(); 
        var price = $form.find('input[name="price"]').val(); 
		var select = $form.find('select[name="booking_code"]');
        var booking_code = select.find('option:selected').text();

        // Display the SweetAlert2 popup
        Swal.fire({
            title: 'Confirmation',
            html: 'Are you sure you want to edit this procedure?<br><br>' +
                '<strong>Name:</strong> ' + name + '<br>' +
                '<strong>Price:</strong> ' + price + ' &euro;<br>' +
                '<strong>Booking Code:</strong> ' + booking_code,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No'
        }).then((result) => {
            // If the user confirms, submit the form
            if (result.isConfirmed) {
                $form.submit();
				// console.log($form);
            }
        });
    });
});
</script>
  