<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Lab report</title>

<style type="text/css">

@page { 
	margin: 120px 35px 150px 35px;
} 

* {
	font-family: Verdana, Arial, sans-serif;
}

.header {
	position: fixed;
	top: -70px;
	left: 0;
	right: 0;
	padding: 10px;
}

body {
	margin: 5px;
	color: #333;
}

/*
    info header
*/
.info-table{
  width:100%;
  border-collapse:collapse;
  font-size:12px;
}
.info-table td{
  vertical-align:top;
  padding:0;
}
.info-label{
  color:#b5b9d3;
}
.info-value{
  color:#555761;
  font-weight:300;
  margin-top:6px;
}
/*

*/

hr {
    margin: 20px 0;
    border: 0;
    border-top: 1px solid #ddd;
}


/*
    table styles
*/
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 10px;
}

table td {
    padding: 0;
    line-height: 0.95;
}

th {
    color: #b6b6b6;
    font-size: 10px;
    font-weight: 600;
    letter-spacing: 1px;
    text-transform: uppercase;
    padding-bottom: 10px;
    margin-bottom: 10px;
	text-align: left;
}
.lab_table td {
    border-bottom:1px solid #eee;
}


/*
 bad value highlighting
*/
.bold {
    color: red;
    font-weight: 600;
}

.break_line
{
    line-height: 0.5em;
}

/*
 fancy chart
*/
.bar {
  width: 150px;
  height: 6px;
  position: relative;
  display: block;
  margin-left: 8px;
  margin-bottom: 3px;
  font-size: 0;
}

.bar .segwrap{
  width: 150px;
  height: 6px;
  border-collapse: separate;
  border-spacing: 3px 0;
}

.bar .segcell{
  width: 33.33%;
  height: 6px;
  border-radius: 3px;
}

.seg.low  { background: #f8d3d3; }
.seg.mid  { background: #e2f5dc; }
.seg.high { background: #f8d3d3; }

/* marker dot/line */
.bar .pos{
  position: absolute;
  top: -2px;
  width: 2px;
  height: 10px;
  background: #333;
}

.text-center {
	text-align: center;
}

.text-danger {
	color: #d9534f;
	font-weight: 600;
}
img {
    max-width: 100%;
    height: auto;
    display: block;
}
</style>


</head>
<body>

<div class="header">
<?php if (file_exists(dirname(__FILE__) . "/../custom/bill_header.php")) { include dirname(__FILE__) . "/../custom/bill_header.php"; }  ?>
</div>

<hr/>
<table class="info-table">
  <tr>
    <td style="width:50%;">
      <div class="info-label"><?= $this->lang->line('pet_info'); ?></div>
      <div class="info-value"><?= htmlspecialchars($lab_info['pet']['name']); ?></div>
    </td>
    <td style="width:50%;">
      <div class="info-label"><?= $this->lang->line('client'); ?></div>
      <div class="info-value"><?= htmlspecialchars($owner['last_name']); ?></div>
    </td>
  </tr>
</table>
<hr>

	
<table width="100%" class="lab_table">
	<thead>
		<tr>
			<th></th>
			<th></th>
			<th><?= $this->lang->line('value'); ?></th>
			<th class="text-center"><?= $this->lang->line('limit'); ?></th>
			<th><?= $this->lang->line('unit'); ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($lab_details as $d): ?>
		<tr>
			<td><?= htmlspecialchars($d['code']); ?></td>

			<td width="30%">
				<?php if ($d['draw_plot']): ?>
					<div class="bar">
					<table class="segwrap" cellspacing="0" cellpadding="0">
						<tr>
						<td class="segcell seg low"></td>
						<td class="segcell seg mid"></td>
						<td class="segcell seg high"></td>
						</tr>
					</table>
					<div class="pos" style="left: <?= (float)$d['pct']; ?>%;"></div>
					</div>
				<?php endif; ?>
			</td>

			<?php if ($d['is_text']): ?>
				<td colspan="3"><?= htmlspecialchars($d['value']); ?></td>
			<?php else: ?>
				<td class="<?= $d['is_out'] ? 'text-danger' : ''; ?>">
					<?= htmlspecialchars($d['value']); ?>
				</td>
				<td class="text-center"><?= htmlspecialchars($d['limit']); ?></td>
				<td><?= htmlspecialchars($d['unit']); ?></td>
			<?php endif; ?>
		</tr>
		<?php endforeach; ?>
		<?php if ($lab_info['device'] == "ms4s2"): ?>
		<?php include "plots/ms4s2_print.php" ?>
		<?php endif; ?>
	</tbody>
</table>

<br/>

</body>

</html>