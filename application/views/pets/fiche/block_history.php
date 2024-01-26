
<!-- phone only links -->
<a href="<?php echo base_url(); ?>events/new_event/<?php echo $pet['id']; ?>" class="btn btn-success mb-3 d-block d-sm-none d-md-none"><i class="fas fa-user-md"></i> <?php echo $this->lang->line('consult'); ?></a>
<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet['id']; ?>" class="btn btn-primary mb-3 d-block d-sm-none d-md-none"><i class="fas fa-tooth"></i> <?php echo $this->lang->line('tooth'); ?></a>
<a href="<?php echo base_url('pets/edit/'. $pet['id']); ?>" class="btn btn-info mb-3 d-block d-sm-none d-md-none"><i class="fas fa-paw"></i> <?php echo $this->lang->line('edit_pet'); ?></a>

<!-- full screen -->
<div class="card mb-4">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		history
	</div>

	<div class="card-body">
		<?php if ($pet_history): ?>
		<table class="table dt-responsive nowrap" style="width:100%" id="dataTable">
		<thead>
			<tr>
				<th><?php echo $this->lang->line('date'); ?></th>
				<th data-priority="2"><?php echo $this->lang->line('title'); ?></th>
				<th><?php echo $this->lang->line('vet'); ?></th>
				<th><?php echo $this->lang->line('location'); ?></th>
				<th data-priority="1"><?php echo $this->lang->line('options'); ?></th>
				<th class="none"><?php echo $this->lang->line('anamnese'); ?></th>
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
		<td>
		<?php if(preg_match('/lab:(\d*)/', $history['title'], $match)): ?>
			<a href="<?php echo base_url('lab/detail/'. (int) $match[1]); ?>" class="btn btn-sm btn-outline-success" style="padding: 0.05rem 0.5rem;" target="_blank"><?php echo get_event_type($history['type']); ?> <?php echo $this->lang->line('Lab'); ?></a>
		<?php else: ?>
			<?php echo get_event_type($history['type']); ?> <?php echo $history['title']; ?>
			<?php if($history['report'] != REPORT_DONE): ?>
				<i class="fas fa-unlock" data-toggle="tooltip" data-placement="top" title="<?php echo $this->lang->line('not_finished'); ?>"></i>
			<?php endif; ?>
		<?php endif; ?>
		</td>
		<td><?php echo (isset($history['vet']['first_name'])) ? 
							$history['vet']['first_name'] 
							. ((isset($history['vet_1_sup'])) ? ', ' . $history['vet_1_sup']['first_name'] : '')
							. ((isset($history['vet_2_sup'])) ? ', ' . $history['vet_2_sup']['first_name'] : '')
							: 'unknown' ; ?></td>
		<td><?php echo (isset($history['location']['name'])) ? $history['location']['name'] : "unknown"; ?></td>
		<td data-search="<?php echo $history['type']; ?>">
			<button class="btn btn-sm btn-outline-primary ana"><i class="fa-solid fa-eye"></i></button>
			<a href="<?php echo base_url('events/event/' . $history['id']); ?>" class="btn btn-sm <?php if($history['report'] == REPORT_DONE): ?>btn-outline-secondary not-allowed<?php else: ?> btn-outline-success<?php endif; ?>"><i class="fa-solid fa-pen"></i></a>
		</td>
		<td>
			<table width="100%">
			<tr>
			<td>
				<?php echo nl2br ($history['anamnese']); ?>
			</td>
			<td>
				<ul>
				<?php foreach($products as $prod) : ?>
					<li><?php echo $prod['volume'] . ' ' . $prod['unit_sell']  . ' ' . $prod['name']; ?></li>
				<?php endforeach; ?>
				<?php foreach($procs as $proc) : ?>
					<li><?php echo $proc['volume'] . ' ' . $proc['name']; ?></li>
				<?php endforeach; ?>
				</ul>
			</td>
			</tr>
				</table>
		</td>
	</tr>
	<?php endfor; ?>
		</tbody>
	</table>

	<?php else : ?>
		<?php echo $this->lang->line('no_history_txt'); ?>
	<?php endif; ?>
	</div>
</div>

<script type="text/javascript">

function searchFor(term, table)
{
	table
		.column(4)
		.search(term)
		.draw();
}

document.addEventListener("DOMContentLoaded", function(){
	var table = $("#dataTable").DataTable({
		"pageLength": 10,
		"order": [[ 0, "desc" ]],
		dom: "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
		buttons: [
			{
				text:'<i class="fas fa-fw fa-virus"></i>', className:'btn btn-outline-primary btn-sm', 
				action: function (e, dt, node, config) {
					searchFor(<?php echo DISEASE; ?>, dt);
				},
				init: function (api, node, config) {
                    $(node).tooltip({
                        title: '<?php echo $this->lang->line('disease'); ?>', placement: 'top', container: 'body'
                    });
                }
			},
			{
				text:'<i class="fas fa-fw fa-hand-holding-medical"></i>', className:'btn btn-outline-primary btn-sm', 
				action: function (e, dt, node, config) {
					searchFor(<?php echo OPERATION; ?>, dt);
				},
				init: function (api, node, config) {
                    $(node).tooltip({
                        title: '<?php echo $this->lang->line('operation'); ?>', placement: 'top', container: 'body'
                    });
                }
			},
			{ text:'<i class="fas fa-fw fa-undo-alt"></i>', className:'btn btn-outline-danger btn-sm', 
				action: function (e, dt, node, config) {
					table.columns().search('').draw();
				}
				
			}
        ],
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
	 ]
	});
		
	$('[data-toggle="tooltip"]').tooltip();
});
</script>