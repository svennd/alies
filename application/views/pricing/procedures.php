<div class="row">
      <div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div>Procedures</div>
				<div class="dropdown no-arrow">
					<a href="#" class="btn btn-outline-success btn-sm" id="add"><i class="fas fa-plus"></i> Add Procedure</a>
				</div>
			</div>
            <div class="card-body">
			
			<div id="add_form" style="display:none;">
				<form method="post" action="<?php echo base_url('pricing/proc'); ?>" class="form-inline confirm-new-procedure">
					<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="" placeholder="procedure name" />
					<div class="input-group mb-2 mr-sm-2">
						<input type="number" step="0.01" min="0" max="1000" class="form-control" name="price" placeholder="price">
						<div class="input-group-append">
							<span class="input-group-text" id="basic-addon2">&euro;</span>
						</div>
					</div>
					<div class="input-group mb-2 mr-sm-2">
						<select name="booking_code" class="form-control" id="booking_code">
							<?php foreach($booking as $t): ?>
								<option value="<?php echo $t['id']; ?>"><?php echo $t['code'] . ' ' . $t['category'] . ' ' . $t['btw']  . '%'; ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<button type="submit" name="submit" value="add_proc" class="btn btn-primary mb-2">Add Procedure</button>
				</form>
			<br/>
			<br/>
			</div>
			<?php if ($proc): ?>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Name</th>
					<th>Price</th>
					<th>Booking</th>
					<th>Options</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($proc as $pro): ?>
				<tr>
					<td><?php echo $pro['name']; ?></td>
					<td><?php echo $pro['price']; ?> &euro;</td>
					<td>
						<?php echo $pro['booking_code']['code']; ?><br>
						<small><?php echo $pro['booking_code']['category']; ?> <?php echo $pro['booking_code']['btw']; ?>%</small>
					</td>
					<td>
						<a href="<?php echo base_url('pricing/proc_edit/' . $pro['id']); ?>" class="edit btn btn-outline-success btn-sm btn-edit"><i class="fas fa-edit"></i></a>
						&nbsp;
						<a href="<?php echo base_url('pricing/delete_proc/' . $pro['id']); ?>" class="btn btn-outline-danger btn-sm delete-confirm"><i class="fas fa-trash-alt"></i></a>
					</td>
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
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	$("#pricingmg").show();
	$("#pricing").addClass('active');
	$("#adminproc").addClass('active');
	
	$("#add").on('click',function(){
		$("#add_form").show();
		$(this).hide();
	});


	// Get all elements with the class 'myLink'
    var links = $('.delete-confirm');
    links.on('click', function(event) {
        event.preventDefault(); 
        
        var link = $(this); 
        Swal.fire({
            title: 'Confirmation',
            text: 'Are you sure you want to delete this procedure?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Delete',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = link.attr('href');
            }
        });
    });

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
  