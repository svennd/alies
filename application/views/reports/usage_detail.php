<?php include "blocks/usage_header.php"; ?>
				<?php if($usage): ?>
				<br>
				<table class="table table-sm" id="dataTable">
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
						<td><?php echo $us['lotnr']; ?> <?php echo ($us['event_location_name'] == $us['stock_location_name']) ? "": '(' . $us['stock_location_name'] . ')'; ?></td>
						<td><?php echo $us['eol']; ?></td>
						<td><?php echo $us['in_price']; ?></td>
						<td><a href="<?php echo base_url('pets/fiche/' . $us['pet_id']); ?>"><?php echo $us['petname']; ?></a></td>
						<td><a href="<?php echo base_url('owners/detail/' . $us['id']); ?>"><?php echo $us['last_name']; ?></a></td>
						<td><?php echo $us['first_name']; ?></td>
						<td><?php echo $us['event_location_name']; ?></td>
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
