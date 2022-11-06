
<!-- phone only links -->
<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user-md"></i> <?php echo $this->lang->line('consult'); ?></a>
<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet['id']; ?>" class="btn btn-primary mb-3 d-block d-sm-none d-md-none"><i class="fas fa-tooth"></i> <?php echo $this->lang->line('tooth'); ?></a>

<!-- full screen -->
<div class="card mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>
			<a href="<?php echo base_url(); ?>owners/detail/<?php echo $owner['id']; ?>"><?php echo $owner['last_name'] ?></a> / 
			<?php echo $pet['name'] ?> <small>(#<?php echo $pet['id']; ?>)</small>
		</div>
		<div class="dropdown no-arrow d-none d-sm-block">

			<div class="btn-group btn-group-sm mr-2" role="group" aria-label="Basic example">
				<a class="btn btn-outline-primary filter" data-search="disease" href="#" role="button"><i class="fas fa-fw fa-virus"></i></a>
				<a class="btn btn-outline-primary filter" data-search="operation" href="#" role="button"><i class="fas fa-fw fa-hand-holding-medical"></i></a>
				<a class="btn btn-outline-primary filter" data-search="medicine" href="#" role="button"><i class="fas fa-fw fa-prescription-bottle-alt"></i></a>
				<a class="btn btn-outline-primary filter" data-search="clear" href="#" role="button"><i class="fas fa-fw fa-undo-alt"></i></a>
			</div>
			<a href="<?php echo base_url(); ?>pets/export/<?php echo $pet['id']; ?>" class="btn btn-outline-info btn-sm ml-5"><i class="fas fa-file-export"></i> <?php echo $this->lang->line('export'); ?></a>
			<a href="<?php echo base_url(); ?>pets/change_owner/<?php echo $pet['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-exchange-alt"></i> <?php echo $this->lang->line('change_owner'); ?></a>
		</div>
	</div>

	<div class="card-body">
		<?php if ($pet_history): ?>
		<table class="table dt-responsive nowrap" style="width:100%" id="dataTable">
		<thead>
			<tr>
				<th><?php echo $this->lang->line('date'); ?></th>
				<th>type</th>
				<th><?php echo $this->lang->line('title'); ?></th>
				<th><?php echo $this->lang->line('vet'); ?></th>
				<th><?php echo $this->lang->line('location'); ?></th>
				<th><?php echo $this->lang->line('edit'); ?></th>
				<th class="none"><?php echo $this->lang->line('anamnese'); ?></th>
				<th class="none">products</th>
			</tr>
		</thead>
	<?php
		for ($i = 0; $i < count($pet_history); $i++) :
			$history = $pet_history[$i];
			$products = (isset($pet_history[$i]['products'])) ? $pet_history[$i]['products']: array();
			$procs = (isset($pet_history[$i]['procedures'])) ? $pet_history[$i]['procedures']: array();
	?>
	<tr>
		<td data-sort="<?php echo strtotime($history['created_at']) ?>"><?php echo user_format_date($history['created_at'], $user->user_date); ?></td>
		<td><?php echo get_event_type($history['type'], true); ?></td>
		<td><?php echo get_event_type($history['type']); ?> <?php echo $history['title']; ?></td>
		<td><?php echo (isset($history['vet']['first_name'])) ? $history['vet']['first_name'] : 'unknown' ; ?></td>
		<td><?php echo (isset($history['location']['name'])) ? $history['location']['name'] : "unknown"; ?></td>
		<td>
			<button class="btn btn-sm btn-outline-primary ana"><?php echo $this->lang->line('show'); ?></button>
			<a href="<?php echo base_url(); ?>events/event/<?php echo $history['id']; ?>" class="btn btn-sm btn-outline-info"><?php echo $this->lang->line('edit'); ?></a></div>
		</td>
		<td><?php echo nl2br ($history['anamnese']); ?></td>
		<td><ul>
			<?php foreach($products as $prod) : ?>
				<li><?php echo $prod['volume'] . ' ' . $prod['unit_sell']  . ' ' . $prod['name']; ?></li>
			<?php endforeach; ?>
			<?php foreach($procs as $proc) : ?>
				<li><?php echo $proc['amount'] . ' ' . $proc['name']; ?></li>
			<?php endforeach; ?>
			</ul>
		</td>
	</tr>
	<?php endfor; ?>
		</tbody>
	</table>

	<?php else : ?>
		<?php echo $this->lang->line('no_history'); ?>
	<?php endif; ?>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	var table = $("#dataTable").DataTable({
		"pageLength": 10, 
		"lengthMenu": [[10, -1], [10, "All"]],
		"order": [[ 0, "desc" ]],
        responsive: {
            details: {
                type: 'column',
                target: 'button'
            }
        },
        columnDefs: [ {
            className: 'dtr-control',
            orderable: false,
            targets: -1
        },
		{ "targets": [ 1 ], "visible": false }
	 ]
	});

	$(".filter").click(function() {
		let search = $(this).data("search");
		$(".filter").removeClass("btn-outline-success");
		$(this).addClass("btn-outline-success");
		if (search == "clear")
		{
			table
			.columns( 1 )
			.search("")
			.draw();
		}
		else{
			table
			.columns( 1 )
			.search(search)
			.draw();
		}
	});
});
</script>