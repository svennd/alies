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
<?php if(is_file("custom/bill_header.php")) { include "custom/bill_header.php"; } ?>
<div class="wrapper">
			
	<table>
	<tr>
		<td valign="top">
			<h3><?php echo $this->lang->line('client'); ?></h3>
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
			<h3><?php echo $this->lang->line('pet_info'); ?></h3>
			<hr />
			<table class="table">
				<tr>
					<td>Type</td>
					<td><?php echo get_name($pet_info['type']); ?></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('gender'); ?></td>
					<td><?php echo get_gender($pet_info['gender']); ?></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('pet_name'); ?></td>
					<td><?php echo $pet_info['name']; ?></td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('birth'); ?></td>
					<td><?php echo $pet_info['birth']; ?></td>
				</tr>
				<?php if ($pet_info['breed']): ?>
				<tr>
					<td><?php echo $this->lang->line('breed'); ?></td>
					<td><?php echo $pet_info['breeds']['name']; ?></td>
				</tr>
				<?php endif; ?>
				<tr>
					<td><?php echo $this->lang->line('weight'); ?></td>
					<td><?php echo $pet_info['last_weight']; ?> kg</td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('chip'); ?></td>
					<td><?php echo $pet_info['chip']; ?></td>
				</tr>
			</table>
		</td>
	</tr>
	</table>
	<br/>
	<br/>
        <?php if($vaccines): ?>
			<table>
			  <thead>
				<tr>
					<th><?php echo $this->lang->line('product'); ?></th>
					<th><?php echo $this->lang->line('injection'); ?></th>
					<th><?php echo $this->lang->line('vet'); ?></th>
				</tr>
			  </thead>
			  <tbody>
				<?php foreach($vaccines as $vac): 
                    if($vac['event_id'] == 0) continue;
                ?>
				<tr>
					<td><?php echo (isset($vac['product']['name'])) ? $vac['product']['name']: $vac['product']; ?></td>
					<td><?php echo user_format_date($vac['created_at'], 'd M, Y'); ?></td>
				  <td><?php echo substr($vac['vet']['first_name'], 0, 1) . '. '. $vac['vet']['last_name']; ?></td>
				</tr>
				<?php endforeach; ?>
			  </tbody>
			</table>
            <?php else: ?>
                <?php echo $this->lang->line('no_vaccines'); ?>
            <?php endif; ?>
			</div>
		<br/>
        <br/>
        <br/>
        <br/>

	</body>

</html>