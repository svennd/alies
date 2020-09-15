<div class="card shadow mb-4">
	<div class="card-header">
		<a href="<?php echo base_url(); ?>report">Report</a> / Vaccine / search expire
	</div>
	<div class="card-body">
		<form action="<?php echo base_url(); ?>reports/vaccine/" method="post" autocomplete="off" class="form-inline">

		  <div class="form-group mb-2 mx-3">
			<label for="staticEmail2" class="sr-only">search_from</label>
			<input type="date" name="search_from" class="form-control" value="<?php echo (isset($search_from)) ? $search_from : ''; ?>" id="search_from">
		  </div>
		  <div class="form-group mb-2">
			<span class="fa-stack" style="vertical-align: top;">
			  <i class="far fa-square fa-stack-2x"></i>
			  <i class="fas fa-arrow-right fa-stack-1x"></i>
			</span>		
		  </div>
		  <div class="form-group mb-2 mx-3">
			<label for="staticEmail2" class="sr-only">search_to</label>
			<input type="date" name="search_to" class="form-control" value="<?php echo (isset($search_to)) ? $search_to : ''; ?>" id="search_to">
		  </div>
		  <button type="submit" class="btn btn-success mb-2">Search range</button>
		</form>
		
		</div>
</div>  
<div class="card shadow mb-4">
	<div class="card-header">
		Vaccine
	</div>
	<div class="card-body">

	<?php if ($vaccines): ?>
		<table class="table" id="dataTable">
		<thead>
		<tr>
			<th>last_name</th>
			<th>first_name</th>
			<th>street</th>
			<th>nr</th>
			<th>city</th>
			<th>zip</th>
			<th>mail</th>
			<th>phone</th>
			<th>contact</th>
			<th>product</th>
			<th>redo</th>
			<th>vet</th>
			<th>location</th>
			<th>created</th>
		</tr>
		</thead>
		<tbody>
		<?php foreach ($vaccines as $vac):
		// var_dump($vac);
			# trick to take the first element from the array
			# the key is $pet_id (its a pivot, many to one)
			$owner = (isset($vac['owners'])) ? array_shift($vac['owners']) : array(); 
		?>
		<tr>
			<td><a href="<?php echo base_url(); ?>owners/detail/<?php echo (isset($owner['owner_id'])) ? $owner['owner_id'] : ''; ?>"><?php echo (isset($owner['last_name'])) ? $owner['last_name'] : ''; ?></td>
			<td><?php echo (isset($owner['first_name'])) ? $owner['first_name'] : ''; ?></td>
			<td><?php echo (isset($owner['street'])) ? $owner['street'] : ''; ?></td>
			<td><?php echo (isset($owner['nr'])) ? $owner['nr'] : ''; ?></td>
			<td><?php echo (isset($owner['city'])) ? $owner['city'] : ''; ?></td>
			<td><?php echo (isset($owner['zip'])) ? $owner['zip'] : ''; ?></td>
			<td><?php echo (isset($owner['mail'])) ? $owner['mail'] : ''; ?></td>
			<td><?php echo (isset($owner['phone'])) ? $owner['phone'] : ''; ?></td>
			<td><?php echo (isset($owner['contact'])) ? $owner['contact'] : ''; ?></td>
			<td><?php echo $vac['product']['name']; ?></td>
			<td><?php echo $vac['redo']; ?></td>
			<td><?php echo $vac['vet']['first_name']; ?></td>
			<td><?php echo (isset($vac['location']['name'])) ? $vac['location']['name']: 'unknown'; ?></td>
			<td>
				<?php echo substr($vac['created_at'], 0, 10); ?><br/>
				<small><?php echo timespan(strtotime($vac['created_at']), time(), 1); ?> Ago</small>
			</td>
		</tr>
		<?php endforeach; ?>
		</tbody>
		</table>
	<?php else: ?>
		No Vaccines found.
	<?php endif; ?>
		</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#reports").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50,  
	"columnDefs": [
    { "type": "num", "targets": 0 }
	],
  "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>
  