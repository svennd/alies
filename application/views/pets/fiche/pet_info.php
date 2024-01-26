<style>
.test {
	background-color: #ffffff;
	box-shadow: 0 0 3px 0 rgba(20,27,202,.17);
	border-radius: 10px;
	padding:15px;
	height: 100%;
}
.notest {
	/* flex: 1 1 auto; */
    padding: 1.25rem;
}
</style>
<div class="test">
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
		<td><?php echo (empty($pet['chip']) || !is_int($pet['chip'])) ? "?" : number_format((int)$pet['chip'], 0, '', '-'); ?></td>
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
	<div class="text-center pt-3">
		<a href="<?php echo base_url('pets/edit/'. $pet['id']); ?>" class="btn btn-outline-primary"><i class="fas fa-fw fa-paw"></i> <?php echo $this->lang->line('edit_pet'); ?></a>
		<a href="<?php echo base_url('tooth/fiche/' . $pet['id']); ?>" class="btn btn-outline-primary ml-4"><i class="fas fa-fw fa-tooth"></i> <?php echo $this->lang->line('tooth'); ?></a>
						
	</div>
</div>