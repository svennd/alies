<div class="row">
	<div class="col-lg-12 mb-4">
	  <div class="card shadow mb-4">
			<div class="card-header py-3">
			  <h6 class="m-0 font-weight-bold text-primary">Procedures</h6>
			</div>
			<div class="card-body">
				<form method="post" action="<?php echo base_url('pricing/proc'); ?>" class="confirm-new-procedure">
					<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="<?php echo $proc['name']; ?>" />
					<div class="input-group mb-2 mr-sm-2">
						<input type="number" step="0.01" min="0" max="1000"  class="form-control" name="price" value="<?php echo $proc['price']; ?>">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro;</span>
						</div>
					</div>
					<div class="input-group mb-2 mr-sm-2">
						<select name="booking_code" class="form-control" id="booking_code">
							<?php foreach($booking as $t): ?>
								<option value="<?php echo $t['id']; ?>" <?php echo ($t['id'] == $proc['booking_code']['id']) ? "selected='selected'":"";?>><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<input type="hidden" name="id" value="<?php echo $proc['id'] ?>">
					<button type="submit" name="submit" value="edit_proc" class="btn btn-primary mb-2">Edit Procedure</button>
				</form>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#adminproc").addClass('active');

	var forms = $('.confirm-new-procedure');
    // Attach an event listener to the submit event of each form
    forms.on('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission
        
        var form = $(this); // Get the submitted form element
                
        var name = form.find('input[name="name"]').val(); 
        var price = form.find('input[name="price"]').val(); 
		var select = form.find('select[name="booking_code"]');
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
                form.unbind('submit').submit(); // Unbind the event listener and submit the form
            }
        });
    });
});
</script>
  