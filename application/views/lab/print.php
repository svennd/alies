<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lab report</title>

<style type="text/css">
    * {
        font-family: Verdana, Arial, sans-serif;
    }
    table{
        font-size: x-small;
		border-collapse: collapse;
    }
    .enlarge {
        font-weight: bold;
        font-size: 18px;
    }
	.letterhead {
		/* font-size: 14px; */
	}
	.nobold {
		font-weight: normal;
	}
    .gray {
        background-color: #eeeeee;
		border-radius: 4px;
    }
	.main_table_header {
		margin-top: 15px;
		border-collapse: collapse;
	}
	.main_table_header th {
		padding: 7px;
	}

	.main_table_header tbody tr:first-of-type {
		padding: 7px;
	}

	.lined {
		padding: 5px;
	}

	footer {
		position: fixed; 
		bottom: -70px; 
		left: 0px; 
		right: 0px;
		height: 130px; 
	}
	
</style>

</head>
<body>
<?php if (file_exists(dirname(__FILE__) . "/../custom/bill_header.php")) { include dirname(__FILE__) . "/../custom/bill_header.php"; }  ?>  
<br/>
<table width="100%">
    <tr>
		<td valign="top" width="30%">
			<span class="enlarge"><?php echo $this->lang->line('lab_report'); ?></span>
			<table>
				<tr>
					<td colspan=2>
					<div style="font-weight: normal;padding:5px;" class="gray letterhead">
						<?php echo $lab_info['lab_comment'] ?>
					</div>
					</td>
				</tr>
				<tr>
					<td><?php echo $this->lang->line('date'); ?></td>
					<td><?php echo user_format_date($lab_info['lab_date'], "d-m-Y"); ?></td>
				</tr>
			</table>
		</td> 
        <td align="left" valign="top" class="letterhead">
			<?php if (isset($owner['id'])): ?>
				<?php echo $owner['last_name']. "&nbsp;" . $owner['first_name']; ?><br/><br/>
					<?php echo $owner['street'] . ' ' . $owner['nr']. '<br/>' .  $owner['zip']. ' ' .  $owner['city']; ?><br>
					<?php if ($owner['telephone']) : ?><?php echo print_phone($owner['telephone']); ?><br/><?php endif; ?>
					<?php if ($owner['mobile']) : ?><?php echo print_phone($owner['mobile']); ?><br/><?php endif; ?>
			<?php endif; ?>
		</td>
        <td align="left" valign="top" class="letterhead">
			<?php if (isset($pet_info['id'])): ?>
			 <table width="100%" class="lined">
			<tr>
				<td>ID</td>
				<td>: #<?php echo $pet_info['id']; ?></td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('breed'); ?></td>
				<td>:
					<?php echo isset($pet_info['breeds']['name']) ? $pet_info['breeds']['name'] : "?"; ?>
					<?php echo isset($pet_info['breeds2']['name']) ? 'x ' . $pet_info['breeds2']['name'] : ""; ?>
				</td>
			</tr>
			<tr>
				<td>Type</td>
				<td>: <?php echo get_name($pet_info['type']); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('gender'); ?></td>
				<td>:<?php echo get_gender($pet_info['gender']); ?></td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('birth'); ?></td>
				<td>: <?php echo $pet_info['birth']; ?></td>
			</tr>
			<tr>
				<td><?php echo $this->lang->line('chip'); ?></td>
				<td>: <?php echo (empty($pet_info['chip']) || !is_numeric($pet_info['chip'])) ? "?" : number_format((int)$pet_info['chip'], 0, '', '-'); ?></td>
			</tr>
			</table>
			<?php endif; ?>
		</td>
    </tr>
  </table>
  <hr style="min-height: 1px; border:0px; background: #DCDDE1;"/>
<br/>
<?php if($lab_info['source'] == "mslink - HEMATO"): ?>
<table style="width:100%">
	<tr>
		<td style="60%">
<?php endif; ?>
			<table style="width:100%" class="main_table_header">
				<thead>
					<tr class="gray">
						<th style="text-align:left"><?php echo $this->lang->line('lab_code'); ?></th>
						<th colspan="3"><?php echo $this->lang->line('value'); ?></th>
						<th><?php echo $this->lang->line('limit'); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php 
					foreach($lab_details as $d):
						if ($d["lab_code"] == "1") continue; // WBC
						if ($d["lab_code"] == "2") continue; // RBC
						if ($d["lab_code"] == "3") continue; // THR

						$lower_limit = $d["lower_limit"];
						$upper_limit = $d["upper_limit"];

						if ($upper_limit == 0 && $lower_limit == 0) {
							$lower_limit = false;
							$upper_limit = false;
						}

						$value = ($d["value"] != 0 && strlen($d["string_value"]) <= 1) ? $d["string_value"] . $d["value"] : $d["string_value"];

						if (is_numeric($value) && $lower_limit !== false && $upper_limit !== false) {
							if ($value < $lower_limit || $value > $upper_limit) {
								$color = "red";
								if ($value < $lower_limit) {
									$sign = "L";
								} else {
									$sign = "H";
								}
							} else {
								$color = "black";
							}
						} else {
							$color = "black";
						}

					?>
					<tr>
						<td><?php echo $d["lab_code_text"]; ?></td>
						<td style="text-align:right"><span style="color:<?php echo $color; ?>">
								<strong><?php echo str_pad($value, 8, " ", STR_PAD_LEFT); ?></strong>
							</span>
						</td>
						<td style="align:left"><span style="color:<?php echo $color; ?>">
								<strong><?php echo $d['unit']; ?></strong>
							</span>
						</td>
						<td style="text-align:left"><small><?php echo ($color == "red") ? $sign : "&nbsp;&nbsp;"; ?></small></td>
						<td style="text-align:right">
							<small><?php echo (strlen($d["string_value"]) <= 1 && $upper_limit) ? $d["lower_limit"] . ' - ' . $d['upper_limit'] : ''; ?></small></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

<?php if($lab_info['source'] == "mslink - HEMATO"): ?>
		</td>
		<td width="10%">&nbsp;</td>
		<td width="30%" valign="top"><br/><br/>
			<img src="<?php echo $wbc_plot; ?>" alt="Line Chart"><br/><br/><br/><br/>
			<img src="<?php echo $rbc_plot; ?>" alt="Line Chart"><br/><br/><br/><br/>
			<img src="<?php echo $thr_plot; ?>" alt="Line Chart"><br/><br/><br/><br/>
		</td>
	</tr>
</table>
<?php endif; ?>

	</body>

</html>