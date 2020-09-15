still used ?
<table>
<tr>
	<td>Ras</td>
	<td><?php echo $pet['breeds']['name']; ?></td>
</tr>
<tr>
	<td>Gewicht</td>
	<td>
		<a href="<?php echo base_url(); ?>pets/history_weight/<?php echo $pet['id']; ?>"><?php echo empty($pet['last_weight']) ? "?" : $pet['last_weight']; ?></a> &nbsp; 
	</td>
</tr>
<tr>
	<td>Gender</td>
	<td><?php 
	switch($pet['gender'])
	{
		case 0: 
			echo "male";
		break;
		case 1:
			echo "female";
		break;
		case 2: 
			echo "male neutered";
		break;
		case 3:
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
	<td><abbr title="<?php echo $pet['birth'] ?>">Birth</abbr></td>
	<td><?php echo timespan(strtotime($pet['birth']), time(), 2); ?></td>
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