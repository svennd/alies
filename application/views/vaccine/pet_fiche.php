<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header">
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $pet_info['owners']['id']; ?>"><?php echo $pet_info['owners']['last_name'] ?></a> / 
			<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet_info['id']; ?>"><?php echo $pet_info['name'] ?></a> / vaccines
		</div>
            <div class="card-body">
			<?php if($vaccines): ?>
			<table class="table" id="dataTable">
			  <thead>
				<tr>
				  <th><?php echo $this->lang->line('vaccines'); ?></th>
				  <th><?php echo $this->lang->line('injection'); ?></th>
				  <th><?php echo $this->lang->line('rappel_date'); ?></th>
				  <th><?php echo $this->lang->line('consult'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
				</tr>
			  </thead>
			  <tbody>
				<?php foreach($vaccines as $vac): ?>
				<tr>
					<td><?php echo $vac['product']['name']; ?></td>
					<td><?php echo user_format_date($vac['created_at'], $user->user_date); ?></td>
					<td><?php echo $vac['redo']; ?></td>
					<td>
						<?php if($vac['event_id'] == 0): ?>
							imported
						<?php else: ?>
							<a href="<?php echo base_url(); ?>events/event/<?php echo $vac['event_id']; ?>"><?php echo $vac['event_id']; ?></a>
						<?php endif; ?>
					</td>
				  <td><?php echo $vac['location']['name']; ?></td>
				  <td><?php echo $vac['vet']['first_name']; ?></td>
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
	$("#clients").addClass('active');

	const current_date = new Date();

	$("#dataTable").DataTable({
			"pageLength": 50,
			"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
			"order": [[ 2, "desc" ]],
			"createdRow": function( row, data, dataIndex){
				var product_date = new Date(data[2]);
				date_diff = (current_date - product_date);
				if( date_diff > 0){
					$(row).addClass("table-secondary");
				/* 30 days */
				} else if (date_diff > -(1000*60*60*24*30)) {
					$(row).addClass("table-danger");
				}
			}
	});
});
</script>
