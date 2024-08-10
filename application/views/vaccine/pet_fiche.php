<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div>
				<a href="<?php echo base_url(); ?>owners/detail/<?php echo $pet_info['owners']['id']; ?>"><?php echo $pet_info['owners']['last_name'] ?></a> / 
				<a href="<?php echo base_url(); ?>pets/fiche/<?php echo $pet_info['id']; ?>"><?php echo $pet_info['name'] ?></a> / <?php echo $this->lang->line('title_vaccines'); ?>
			</div>
			<div class="dropdown no-arrow d-none d-sm-block">
				<a href="<?php echo base_url('vaccine/add_martian_vaccine/' . $pet_info['id']); ?>" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-cloud-arrow-up"></i> <?php echo $this->lang->line('add'); ?></a>
			</div>
		</div>
            <div class="card-body">
			<?php if($vaccines): ?>
			<table class="table table-sm" id="dataTable">
			  <thead>
				<tr>
					<th><?php echo $this->lang->line('vaccines'); ?></th>
					<th><?php echo $this->lang->line('injection'); ?></th>
					<th><?php echo $this->lang->line('rappel_date'); ?></th>
					<th><?php echo $this->lang->line('consult'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
					<th><?php echo $this->lang->line('location'); ?></th>
					<th><?php echo $this->lang->line('option'); ?></th>
				</tr>
			  </thead>
			  <tbody>
				<?php foreach($vaccines as $vac): ?>
				<tr>
					<td><?php echo (isset($vac['product']['name'])) ? $vac['product']['name']: $vac['product']; ?></td>
					<td data-sort="<?php echo strtotime($vac['created_at']); ?>"><?php echo user_format_date($vac['created_at'], $user->user_date); ?></td>
					<td data-sort="<?php echo strtotime($vac['redo']); ?>">
					<?php if (!$vac['no_rappel']): ?>
						<?php echo user_format_date($vac['redo'], $user->user_date); ?>
					<?php else: ?>
						<i class="fa-regular fa-circle-xmark"></i>
					<?php endif; ?>
					</td>
					<td>
						<?php if($vac['event_id'] == 0): ?>
							imported
						<?php else: ?>
							<a href="<?php echo base_url(); ?>events/event/<?php echo $vac['event_id']; ?>"><?php echo $vac['event_id']; ?></a>
						<?php endif; ?>
					</td>
				  <td><?php echo $vac['vet']['first_name']; ?></td>
				  <td><?php echo $vac['location']['name']; ?></td>
				  <td><a href="<?php echo base_url('vaccine/detail/' . $vac['id']); ?>"><i class="fa-regular fa-pen-to-square"></i></a></td>
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

	const current_date = new Date();

	$("#dataTable").DataTable({
			"order": [[ 1, "desc" ]],
			"createdRow": function( row, data, dataIndex){
				var product_date = new Date(data[2]["@data-sort"]*1000);
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
