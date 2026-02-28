<?php include 'blocks/menu.php'; ?>
<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>Vamreg / in</div>
        <div>
            <a href="<?= base_url("vamreg/index/$prevY/$prevQ") ?>"
            class="btn btn-outline-success btn-sm">
                <i class="fas fa-angle-double-left fa-fw"></i>
                Q<?= $prevQ ?> <?= $prevY ?>
            </a>

            <span class="mx-2 font-weight-bold">
                Q<?= $quarter ?> <?= $year ?>
            </span>

            <?php if (!$isCurrentQuarter): ?>
                <a href="<?= base_url("vamreg/index/$nextY/$nextQ") ?>"
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

        <?php if($in_buffer): ?>
            <table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th>Status</th>
					<th>Delivery</th>
					<th>Volume</th>
					<th>Product</th>
					<th>Options</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($in_buffer as $buff): ?>
				<tr>
                    <td data-sort="<?php echo strtotime($buff['delivery']); ?>"><a href="<?php echo base_url("wholesale/delivery/". substr($buff['delivery'], 0, 10)); ?>"><?php echo user_format_date($buff['delivery'], $user->user_date); ?></a></td>
					<td><?php include 'blocks/status.php'; ?></td>
                    <td><?php echo $buff['in_quantity_pack_count']; ?></td>
                    <td>
                        <?php if (isset($buff['vamreg_index'])): ?>
                            <!-- automation possible -->
                            <?php if ( count($buff['vamreg_index']) == 1): ?>
                                <!-- <?php echo $buff['vamreg_index']['0']['ppnNL'] ?> -->
                                 <?php echo $buff['wholesale']['description']; ?>
                            <!-- selection required -->
                            <?php else: ?>
                                GROOTHANDEL : <?php echo $buff['wholesale']['description']; ?><br/>
                                <?php foreach ($buff['vamreg_index'] as $prod): ?>
                                    VAMREG : <?php echo $prod['ppnNL'] ?> - <?php echo $prod['packSize'] ?><br/>
                                    <?php echo $prod['maName'] ?> - <?php echo $prod['maNumber'] ?><br/>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($buff['status'] == 'DRAFT'): ?>
                            <a href="<?php echo base_url("vamreg/edit/". $buff['id']); ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen-to-square" style="color: #0d6efd;"></i> edit</a>
							<a href="<?php echo base_url("vamreg/lock/". $buff['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash" style="color: #dc3545;"></i></a>
						<?php elseif ($buff['status'] === 'INVALID'): ?>
							<a href="<?php echo base_url("vamreg/unlock/". $buff['id']); ?>" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-undo" style="color: #28a745;"></i></a>
						<?php endif; ?>
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
	$("#vamreg-in").addClass('active');
});
</script>