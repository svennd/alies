<div class="row">
	<div class="col-lg-10 mb-4">
	<div class="card mb-4">
		<div class="card-header py-3">
			<a href="<?php echo base_url(); ?>pets">Pets</a> / Search
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
	<?php if (isset($card)) : $owner = $card['owners']; ?>
	<div class="card mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Search result</h6>
		</div>
		<div class="card-body">
			<table class="table">
			<tr>
				<td>name (age)</td>
				<td><?php echo $card['name']; ?> (<?php echo timespan(strtotime($card['birth']), time(), 1); ?>)</td>
			</tr>
			<tr>
				<td>breed</td>
				<td><?php echo $card['breeds']['name']; ?></td>
			</tr>
			<tr>
				<td>color</td>
				<td><?php echo $card['color']; ?></td>
			</tr>
			<tr>
				<td>nr_vac_book</td>
				<td><?php echo $card['nr_vac_book']; ?></td>
			</tr>
			<tr>
				<td>owner name</td>
				<td><a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['first_name']; ?> <?php echo $owner['last_name']; ?></a></td>
			</tr>
			<tr>
				<td>Phone</td>
				<td>
					<?php if ($owner['telephone']) : ?><abbr title="Phone">P:</abbr> <?php echo $owner['telephone']; ?><br/><?php endif; ?>
					<?php if ($owner['mobile']) : ?><abbr title="Mobile">M:</abbr> <?php echo $owner['mobile']; ?><?php endif; ?>
				</td>
			</tr>
			<tr>
				<td>Address</td>
				<td>
					<?php echo $owner['street']; ?> <?php echo $owner['nr']; ?><br>
					<?php echo $owner['zip']; ?> <?php echo $owner['city']; ?>
				</td>
			</tr>
			</table>
		</div>
	</div>
	<?php endif;?>
	<?php if (isset($result) && $result):  ?>
	<div class="card mb-4">
		<div class="card-header py-3">
			<h6 class="m-0 font-weight-bold text-primary">Search result</h6>
		</div>
		<div class="card-body">
				<table class="table" id="dataTable">
				<thead>
				<tr>
					<th>Pet Name</th>
					<th>Adress</th>
					<th>City</th>
					<th>Pet(s)</th>
					<th>option</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($result as $res): ?>
				<tr>
					<td>
						<?php echo $res['name']; ?> (<?php echo timespan(strtotime($res['birth']), time(), 1); ?>)
					</td>
					<td>
					<div class="row">
						<div class="col">
						<?php echo $res['street']; ?>
						</div>
						<div class="col">
						<?php echo $res['nr']; ?>
						</div>
					</div>
					</td>
					<td>
						<?php echo $res['city']; ?>
					</td>
					<td>
						<?php
							if ($res['pets'])
							{
								if (count($res['pets']) == 1)
								{
									echo $res['pets']['0']['name'];
								}
								else									
								{
									echo count($res['pets']);
								}
							}

						?>
					</td>
					<td>
						<a href="<?php echo base_url() . 'owners/detail/' . $res['id']; ?>"><i class="fas fa-fw fa-dog"></i></a>
						<a href="<?php echo base_url() . 'owners/edit/' . $res['id']; ?>"><i class="fas fa-edit"></i></a>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
			</div>
		</div>
		<?php endif; ?>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#pets").addClass('active');
	$("#dataTable").DataTable({"pageLength": 50, "lengthMenu": [[50, 100, -1], [50, 100, "All"]]});
});
</script>