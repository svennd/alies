<?php include 'blocks/menu.php'; ?>
<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>Vamreg / out</div>
        <div>
            <a href="<?= base_url("vamreg/out/$prevY/$prevQ") ?>"
            class="btn btn-outline-success btn-sm">
                <i class="fas fa-angle-double-left fa-fw"></i>
                Q<?= $prevQ ?> <?= $prevY ?>
            </a>

            <span class="mx-2 font-weight-bold">
                Q<?= $quarter ?> <?= $year ?>
            </span>

            <?php if (!$isCurrentQuarter): ?>
                <a href="<?= base_url("vamreg/out/$nextY/$nextQ") ?>"
                class="btn btn-outline-success btn-sm">
                    Q<?= $nextQ ?> <?= $nextY ?>
                    <i class="fas fa-angle-double-right fa-fw"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>
	<div class="card-body">
		<!-- alert on post -->
		<?php include 'blocks/vamreg_status.php'; ?>

        <?php if($out_buffer): ?>
            <table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th>Status</th>
					<th>Volume</th>
					<th>Product</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($out_buffer as $buff): ?>
				<tr>
					<td><?php include 'blocks/status.php'; ?></td>
                    <td>
						<a href="<?php echo base_url('vamreg/out_detail/' . $buff['cnk']) . '/'. $year . '/' . $quarter; ?>">
							<?php echo $buff['total_quantity']; ?>
							<?php echo ($buff['out_quantity_type'] == "PACKS") ? " PACKS" : " " . $buff['out_quantity_unit']; ?>
						</a>
						</td>
					<td>
						<?php if ($buff['has_negative']): ?>
							<i class="fa-solid fa-triangle-exclamation" title="Negative quantity detected" style="color: #f8a4ab;"></i>
						<?php endif; ?>
						<?php echo $buff['wholesale_description']; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
        <?php endif;?>
 	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	$("#dataTable").DataTable();
	$("#vamreg-out").addClass('active');
});
</script>