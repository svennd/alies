<div class="card shadow mb-4">
	<div class="card-header"><a href="<?php echo base_url(); ?>pets/edit/<?php echo $pet['id']; ?>">Pet info</a></div>
	<div class="card-body">
		<table class="table">
<tr>
	<td>ID</td>
	<td>#<?php echo $pet['id']; ?></td>
</tr>
<tr>
	<td>Ras</td>
	<td><?php echo $pet['breeds']['name']; ?></td>
</tr>
<tr>
	<td>Gender</td>
	<td><?php 
	switch($pet['gender'])
	{
		case MALE: 
			echo "male";
		break;
		case FEMALE:
			echo "female";
		break;
		case MALE_NEUTERED: 
			echo "male neutered";
		break;
		case FEMALE_NEUTERED:
			echo "female neutered";
		break;
		default:
			echo "other";
	}
	?></td>
</tr>
<tr>
	<td>Haarkleur</td>
	<td><?php echo empty($pet['color']) ? "?" : $pet['color']; ?></td>
</tr>
<tr>
	<td>Identificatie Nummer</td>
	<td><?php echo empty($pet['chip']) ? "?" : $pet['chip']; ?></td>
</tr>
<tr>
	<td>Nummer Vaccinatie Boekje</td>
	<td><?php echo empty($pet['nr_vac_book']) ? "?" : $pet['nr_vac_book']; ?></td>
</tr>
</table>
<?php if (!empty($pet['note'])): ?>
	<div class="card bg-warning text-white">
		<div class="card-body">
		<div class="text-white"><?php echo empty($pet['note']) ? "?" : $pet['note']; ?></div>
		</div>
	</div>
	<br/>
<?php endif; ?>

	</div>
</div>
