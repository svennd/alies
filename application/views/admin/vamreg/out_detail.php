<?php include 'blocks/menu.php'; ?>
<div class="card shadow mb-4">
    <div class="card-header d-flex flex-row align-items-center justify-content-between">
        <div>Vamreg / out / <?php echo $entries[0]['product_name']; ?> (<?php echo $entries[0]['cnk']; ?>)</div>
    </div>
	<div class="card-body">
        <?php if($entries): ?>
            <table class="table table-sm" id="dataTable">
				<thead>
				<tr>
					<th>out_date</th>
					<th>Status</th>
					<th>event</th>
					<th>quantity</th>
					<th>vet</th>
					<th>options</th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($entries as $buff): ?>
				<tr>
					<td data-sort="<?php echo strtotime($buff['out_date']); ?>"><?php echo $buff['out_date']; ?></td>
					<td><?php include 'blocks/status.php'; ?></td>
					<td>
						<a href="<?php echo base_url("events/event/" . $buff['event']); ?>"><?php echo $buff['pet_name']; ?></a> - <?php echo $buff['owner'] ?><br/>
						<small><i><?php echo $buff['target_species']; ?> - <?php echo $buff['indication']; ?></i></small>
					</td>
					<td><?php echo $buff['total_quantity']; ?> <?php echo $buff['unit']; ?></td>
					<td><?php echo $buff['vet_name']; ?></td>
					<td>
						<a href="<?php echo base_url("vamreg/edit_out/". $buff['id']); ?>" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-pen-to-square" style="color: #0d6efd;"></i> edit</a>
						<?php if ($buff['status'] !== 'INVALID'): ?>
							<a href="<?php echo base_url("vamreg/remove/". $buff['id'] . '/' . $buff['cnk'] . '/' . $year . '/' . $quarter); ?>" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-trash" style="color: #dc3545;"></i></a>
						<?php elseif ($buff['status'] === 'INVALID'): ?>
							<a href="<?php echo base_url("vamreg/restore/". $buff['id'] . '/' . $buff['cnk'] . '/' . $year . '/' . $quarter); ?>" class="btn btn-outline-success btn-sm"><i class="fa-solid fa-undo" style="color: #28a745;"></i></a>
						<?php endif; ?>
					</td>
				</tr>
				<?php endforeach; ?>
				</tbody>
				</table>
        <?php endif;?>
        <a href="<?php echo base_url('vamreg/reset'); ?>" class="btn btn-danger btn-sm"> Reset</a>
        <a href="<?php echo base_url('vamreg/post_all/'. $year . '/' . $quarter); ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-paper-plane" style="color: #14bce6;"></i> Post All</a>
    </div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');
	$("#dataTable").DataTable();
	$("#vamreg-out").addClass('active');
});
</script>