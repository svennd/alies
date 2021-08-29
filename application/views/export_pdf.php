<?php 
function get_pet_type($type)
{
	switch($type)
	{
		case DOG:
			return 'dog';
		case CAT:
			return 'cat';
		case HORSE:
			return 'horse';
		case BIRD:
			return 'bird';
		case RABBIT:
			return 'rabbit';
		default:
			return 'Other';
	}						
}
function get_pet_gender($gender)
{
	switch($gender)
	{
		case MALE:
			return 'Male';
		case FEMALE:
			return 'Male neutered';
		case MALE_NEUTERED:
			return 'Female';
		case FEMALE_NEUTERED:
			return 'Female neutered';
		default:
			return 'Other';
	}						
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Export Pet</title>
<style>
table {
  border-spacing: 1;
  border-collapse: collapse;
  background: white;
  overflow: hidden;
  max-width: 800px;
  border-radius: 3px;
  width: 100%;
  margin: 0 auto;
  position: relative;
}
table * {
  position: relative;
}
table td, table th {
  padding-left: 8px;
}
table thead tr {
  height: 60px;
  background: #FFED86;
  font-size: 16px;
}
table tbody tr {
  height: 48px;
  border-bottom: 1px solid #E3F1D5;
}
table tbody tr:last-child {
  border: 0;
}
table td, table th {
  text-align: left;
}
table td.l, table th.l {
  text-align: right;
}
table td.c, table th.c {
  text-align: center;
}
table td.r, table th.r {
  text-align: center;
}

@media screen and (max-width: 35.5em) {
  table {
    display: block;
  }
  table > *, table tr, table td, table th {
    display: block;
  }
  table thead {
    display: none;
  }
  table tbody tr {
    height: auto;
    padding: 8px 0;
  }
  table tbody tr td {
    padding-left: 45%;
    margin-bottom: 12px;
  }
  table tbody tr td:last-child {
    margin-bottom: 0;
  }
  table tbody tr td:before {
    position: absolute;
    font-weight: 700;
    width: 40%;
    left: 10px;
    top: 0;
  }

}
body {
  font: 400 14px 'Calibri','Arial';
  padding: 20px;
}

blockquote {
  color: white;
  text-align: center;
}

.vet {
	text-align: right;
}

</style>
</head>
<body>
<?php include "custom/bill_header.php"; ?>
<div class="wrapper">
			
	<table>
	<tr>
		<td valign="top">
			<h3>Owner</h3>
			<hr />
			<table class="table">
				<tr>
					<td>
						<b><?php echo $owner['last_name']. "&nbsp;" . $owner['first_name']; ?></b><br/>
						<?php echo $owner['street'] . ' ' . $owner['nr']. '<br/>' .  $owner['zip'] . ' ' .  $owner['city']; ?><br>
						<?php if ($owner['telephone']) : ?>tel. : <?php echo $owner['telephone']; ?><br/><?php endif; ?>
						<?php if ($owner['mobile']) : ?>mobile : <?php echo $owner['mobile']; ?><br/><?php endif; ?>
					</td>
				</tr>
			</table>
		</td>
		<td>
			<h3>Pet</h3>
			<hr />
			<table class="table">
				<tr>
					<td>Type</td>
					<td><?php echo get_pet_type($pet_info['type']); ?></td>
				</tr>
				<tr>
					<td>Gender</td>
					<td><?php echo get_pet_gender($pet_info['gender']); ?></td>
				</tr>
				<tr>
					<td>Name</td>
					<td><?php echo $pet_info['name']; ?></td>
				</tr>
				<tr>
					<td>Birth</td>
					<td><?php echo $pet_info['birth']; ?></td>
				</tr>
				<tr>
					<td>Breed</td>
					<td><?php echo $pet_info['breeds']['name']; ?></td>
				</tr>
				<tr>
					<td>Last Weight</td>
					<td><?php echo $pet_info['last_weight']; ?> kg</td>
				</tr>
				<tr>
					<td>chip</td>
					<td><?php echo $pet_info['chip']; ?></td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	<br/>
	<br/>
	<hr>	
		<?php  $i = 1; foreach ($pet_history as $his): 
			if (isset($history_to_take) && !in_array($his['id'], $history_to_take)) { continue; } 
		?>
		<table>
			<thead>
				<tr>
					<th>Report : <?php echo date_format(date_create_from_format ('Y-m-d H:i:s', $his['created_at']), 'd/m/Y'); ?></th>
					<th align="right" class="vet">Vet</th>
				</tr>
			</thead>
			<tr>
				<td valign="top" style="size:1.1em;" width="75%">
				<br/>
				<?php echo nl2br($his['anamnese']); ?></td>
				<td valign="top"  class="vet" align="right"><br/><?php echo $his['vet']['first_name']. ' ' . $his['vet']['last_name']; ?></td>
			</tr>
		</table>
		<br/>
		<?php endforeach; ?>
<br/>
<br/>
<br/>
<br/>

	</body>

</html>