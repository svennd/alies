
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card mb-4">
			<div class="card-header">
				<a href="<?php echo base_url(); ?>admin/breeds">Breeds</a> / Search
			</div>
			<div class="card-body">
			<p>only showing alive pets : <?php echo count($breeds) ;?></p>
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Pet</th>
					<th>Client</th>
					<th>Adress</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($breeds as $res): ?>
				<tr>
					<td>
						<a href="<?php echo base_url() . 'pets/fiche/' . $res['id']; ?>" class="text-nowrap">
							<?php echo $res['name']; ?>
						</a>
					</td>
					<td><a href="<?php echo base_url() . 'owners/detail/' . $res['owners']['id']; ?>"><?php echo $res['owners']['last_name']; ?></a></td>
					<td>
					<div class="row">
						<div class="col">
						<?php echo $res['owners']['street']; ?>
						</div>
						<div class="col">
						<?php echo $res['owners']['city']; ?>
						</div>
					</div>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			</div>
		</div>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){

	var dt = $("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
	
});
</script>