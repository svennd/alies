
<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header">
				medilab
			</div>
			<div class="card-body">
				<table class="table" id="dataTable">
					<thead>
						<tr>
							<th>id</th>
							<th>bovenlimiet</th>
							<th>commentaar</th>
							<th>decimalen</th>
							<th>eenheid</th>
							<th>ingegeven</th>
							<th>labo_id</th>
							<th>lengte</th>
							<th>onderlimiet</th>
							<th>rapport</th>
							<th>resultaat</th>
							<th>sp_resultaat</th>
							<th>staal_id</th>
							<th>svorig</th>
							<th>table</th>
							<th>tabulatie_code</th>
							<th>tek_resultaat</th>
							<th>test_id</th>
							<th>updated_at</th>
						</tr>
					</thead>
					<tbody>
						<?php foreach($data as $res): $i =1 ;foreach($res as $d): ?>
						<tr>
							<td><?php echo $i; ?></td>
							<td><?php echo $d["bovenlimiet"]; ?></td>
							<td><?php echo $d["commentaar"]; ?></td>
							<td><?php echo $d["decimalen"]; ?></td>
							<td><?php echo $d["eenheid"]; ?></td>
							<td><?php echo $d["ingegeven"]; ?></td>
							<td><?php echo $d["labo_id"]; ?></td>
							<td><?php echo $d["lengte"]; ?></td>
							<td><?php echo $d["onderlimiet"]; ?></td>
							<td><?php echo $d["rapport"]; ?></td>
							<td><?php echo $d["resultaat"]; ?></td>
							<td><?php echo $d["sp_resultaat"]; ?></td>
							<td><?php echo $d["staal_id"]; ?></td>
							<td><?php echo $d["svorig"]; ?></td>
							<td><?php echo $d["table"]; ?></td>
							<td><?php echo $d["tabulatie_code"]; ?></td>
							<td><?php echo $d["tek_resultaat"]; ?></td>
							<td><?php echo $d["test_id"]; ?></td>
							<td><?php echo $d["updated_at"]; ?></td>
						</tr>
						<?php $i++; endforeach; ?>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#dataTable").DataTable();
	$("#adminmgm").show();
	$("#admin").addClass('active');
	$("#adminlocation").addClass('active');
});
</script>