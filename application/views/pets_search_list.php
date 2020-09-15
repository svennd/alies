<div class="row">
      <div class="col-lg-12 mb-4">

      <div class="card shadow mb-4">
		<div class="card-header py-3">
			<a href="<?php echo base_url(); ?>pets">Pets</a> / Search by Name
		</div>
				<div class="card-body">
					<form action="<?php echo base_url(); ?>pets" method="post" autocomplete="off" class="form-inline">
					<label class="sr-only" for="chip">Chip</label>
					<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($chip)) ? "is-valid" : "" ?>" id="chip" name="chip" autocomplete="dezzd" placeholder="<?php echo (isset($chip)) ? $chip : "Chip nr" ?>">
					<button type="submit" name="submit" value="1" class="btn btn-primary mb-2">Search</button>
					</form>
					<form action="<?php echo base_url(); ?>pets" method="post" autocomplete="off" class="form-inline">
					<label class="sr-only" for="Name">Name</label>
					<input type="text" class="form-control mb-2 mr-sm-2 <?php echo (isset($name)) ? "is-valid" : "" ?>" id="name" name="name" autocomplete="dezzd" placeholder="<?php echo (isset($name)) ? $name : "name" ?>">
					<button type="submit" name="submit" value="2" class="btn btn-primary mb-2">Search</button>
					</form>
		</div>

	</div>
		<div class="card mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Search result</h6>
		</div>
		<div class="card-body">
			<?php if ($pets): ?>
			
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>name (age)</th>
					<th>breed</th>
					<th>color</th>
					<th>nr_vac_book</th>
					<th>last_bill</th>
					<th>owner</th>
					<th>address</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($pets as $pet): ?>
				<tr>
					<td><a href="<?php echo base_url(); ?>/owners/detail/<?php echo $pet['owners']['id']; ?>"><?php echo $pet['name']; ?></a> (<?php echo timespan(strtotime($pet['birth']), time(), 1); ?>)</td>
					<td><?php echo $pet['breeds']['name']; ?></td>
					<td><?php echo $pet['color']; ?></td>
					<td><?php echo $pet['nr_vac_book']; ?></td>
					<td><?php echo $pet['owners']['last_bill']; ?></td>
					<td><a href="<?php echo base_url(); ?>/owners/detail/<?php echo $pet['owners']['id']; ?>"><?php echo $pet['owners']['last_name']; ?></a></td>
					<td>
					<?php echo $pet['owners']['street']; ?> <?php echo $pet['owners']['nr']; ?><br>
					<?php echo $pet['owners']['city']; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			<?php else: ?>
				No results
			<?php endif; ?>
                </div>
		</div>

      
</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#pets").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>