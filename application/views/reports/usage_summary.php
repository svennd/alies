<?php include "blocks/usage_header.php"; ?>

				<?php if($usage): ?>
				<br>
				<table class="table table-sm">
				<tr>
					<th>Location</th>
					<th>Volume</th>
					<th>Netto</th>
					<!-- <th>Bruto</th> -->
					<th># Transactions</th>
					<th>TIER (*)</th>
					<th>AUTO (*)</th>
					<th>MANUAL (*)</th>
				</tr>
				<?php foreach ($usage as $row): ?>
					<tr>
						<td><?php echo ($row['location']); ?></td>
						<td><?php echo ($row['total_volume']). ' ' . $prod_info['unit_sell'];; ?></td>
						<td><?php echo ($row['total_netto_price']); ?></td>
						<!-- <td><?php echo ($row['total_bruto_price']); ?></td> -->
						<td><?php echo ($row['total_transactions']); ?></td>
						<td><?php echo ($row['tier_reduction_count']); ?></td>
						<td><?php echo ($row['auto_reduction_count']); ?></td>
						<td><?php echo ($row['manual_reduction_count']); ?></td>
					</tr>
				<?php endforeach; ?>
			</table>
			<small>
			(*) Reduction : count of reductions applied. 
			<ul>
				<li>tier -> sell at higher as if higher volume
				<li>auto -> click x% reduction button
				<li>manual set price manuall
			</ul>
				<?php endif; ?>
                </div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#reportsmgm").addClass('active');
	$("#rep").show();
	$("#products_report").addClass('active');
	$("#dataTable").DataTable();
});
</script>
