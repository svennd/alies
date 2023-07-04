<div class="card shadow mb-4">

	<div class="card-header d-flex flex-row align-items-center justify-content-between">
			<div><?php echo $this->lang->line('pet_info'); ?></div>
			<div class="dropdown no-arrow d-none d-sm-block">
				<a href="<?php echo base_url(); ?>tooth/fiche/<?php echo $pet['id']; ?>" class="btn btn-primary btn-icon-split btn-sm"><span class="icon text-white-50"><i class="fas fa-tooth"></i></span><span class="text"><?php echo $this->lang->line('tooth'); ?></span></a>
				<a href="<?php echo base_url('pets/edit/'. $pet['id']); ?>" class="btn btn-info btn-icon-split btn-sm"><span class="icon text-white-50"><i class="fas fa-paw"></i></span><span class="text"><?php echo $this->lang->line('edit_pet'); ?></span></a>
			</div>
		</div>
	<div class="card-body">
		<table class="table">
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
	<td><?php echo $this->lang->line('gender'); ?></td>
	<td><?php echo get_gender($pet['gender']); ?></td>
</tr>
<tr>
	<td><?php echo $this->lang->line('haircolor'); ?></td>
	<td><?php echo empty($pet['color']) ? "?" : $pet['color']; ?></td>
</tr>
<tr>
	<td><?php echo $this->lang->line('chip'); ?></td>
	<td><?php echo empty($pet['chip']) ? "?" : $pet['chip']; ?></td>
</tr>
<tr>
	<td><?php echo $this->lang->line('vacc_nr'); ?></td>
	<td><?php echo empty($pet['nr_vac_book']) ? "?" : $pet['nr_vac_book']; ?></td>
</tr>
</table>
<?php if (!empty($pet['note'])): ?>
	<div class="card bg-warning text-white">
		<div class="card-body">
		<div class="text-white"><?php echo empty($pet['note']) ? "?" : nl2br($pet['note']); ?></div>
		</div>
	</div>
	<br/>
<?php endif; ?>
	</div>
</div>
