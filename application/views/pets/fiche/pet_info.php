<div class="card shadow mb-4" style="width:100%;">
	<div class="card-header d-flex flex-row align-items-center justify-content-between">
		<div>
			<a href="<?php echo base_url('owners/detail/' . $owner['id']); ?>"><?php echo $owner['last_name'] ?></a> / 
			<?php echo $pet['name'] ?> <small>(#<?php echo $pet['id']; ?>)</small>
		</div>
		<div class="dropdown no-arrow d-none d-sm-block">
			<a href="<?php echo base_url('pets/export/' . $pet['id']); ?>" class="btn btn-outline-info btn-sm ml-5"><i class="fas fa-file-export"></i> <?php echo $this->lang->line('export'); ?></a>
			<a href="<?php echo base_url('pets/change_owner/' . $pet['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-exchange-alt"></i> <?php echo $this->lang->line('change_owner'); ?></a>
		</div>
	</div>
	<div class="card-body">

	<table class="table table-sm">
	<tr>
		<td>ID</td>
		<td>#<?php echo $pet['id']; ?></td>
	</tr>
	<tr>
		<td><?php echo $this->lang->line('breed'); ?></td>
		<td>
			<?php echo get_symbol($pet['type']); ?>
			<?php echo isset($pet['breeds']['name']) ? $pet['breeds']['name'] : "?"; ?>
			<?php echo isset($pet['breeds2']['name']) ? 'x ' . $pet['breeds2']['name'] : ""; ?>
		</td>
	</tr>
	<tr>
		<td>Type</td>
		<td><?php echo get_name($pet['type']); ?></td>
	</tr>
	<tr>
		<td><?php echo $this->lang->line('gender'); ?></td>
		<td><?php echo get_gender($pet['gender']); ?></td>
	</tr>
	<?php if (!empty($pet['color'])): ?>
	<tr>
		<td><?php echo $this->lang->line('haircolor'); ?></td>
		<td><?php echo empty($pet['color']) ? "?" : $pet['color']; ?></td>
	</tr>
	<?php endif; ?>
	<?php if (!empty($pet['hairtype'])): ?>
	<tr>
		<td><?php echo $this->lang->line('hairtype'); ?></td>
		<td><?php echo empty($pet['hairtype']) ? "?" : $pet['hairtype']; ?></td>
	</tr>
	<?php endif; ?>
	<tr>
		<td><?php echo $this->lang->line('weight'); ?></td>
		<td><a href="<?php echo base_url('pets/history_weight/' . $pet['id']); ?>"><?php echo ($pet['last_weight'] == 0) ? "---" : $pet['last_weight'] . 'kg'; ?></td>
	</tr>
	<tr>
		<td><?php echo $this->lang->line('birth'); ?></td>
		<td><?php echo user_format_date( $pet['birth'], $user->user_date); ?><br/><small><?php if(!$pet['death']): ?><?php echo timespan(strtotime($pet['birth']), time(), 1); ?><?php endif; ?></small></td>
	</tr>
	<tr>
		<td><?php echo $this->lang->line('chip'); ?></td>
		<td><?php echo (empty($pet['chip']) || !is_numeric($pet['chip'])) ? "?" : number_format((int)$pet['chip'], 0, '', '-'); ?></td>
	</tr>
	<?php if (!empty($pet['nr_vac_book'])): ?>
	<tr>
		<td><?php echo $this->lang->line('vacc_nr'); ?></td>
		<td><?php echo empty($pet['nr_vac_book']) ? "?" : $pet['nr_vac_book']; ?></td>
	</tr>
	<?php endif; ?>
	</table>
	<?php if (!empty($pet['note'])): ?>
		<div class="card bg-warning text-white">
			<div class="card-body">
			<div class="text-white"><?php echo empty($pet['note']) ? "?" : nl2br($pet['note']); ?></div>
			</div>
		</div>
		<br/>
	<?php endif; ?>
	<div class="text-center">
		<a href="<?php echo base_url('pets/edit/'. $pet['id']); ?>" class="btn btn-outline-primary"><i class="fas fa-fw fa-paw"></i> <?php echo $this->lang->line('edit_pet'); ?></a>
		<a href="<?php echo base_url('tooth/fiche/' . $pet['id']); ?>" class="btn btn-outline-primary ml-4"><i class="fas fa-fw fa-tooth"></i> <?php echo $this->lang->line('tooth'); ?></a>
		<a href="<?php echo base_url('rx/list/'. $pet['id'] ); ?>" class="btn btn-outline-primary ml-4"><i class="fa-solid fa-radiation"></i> <?php echo $this->lang->line('rx'); ?></a>
	</div>

	</div>
</div>