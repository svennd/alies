
<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
		Vaccines - <?php echo $month . ' ' . $year; ?> (<?php echo count($expiring_vacs); ?>)
			<div class="dropdown no-arrow">
				<a href="<?php echo base_url('vaccine/index/' . $month_int. '/export'); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-file-export"></i> export csv : <?php echo $month . ' ' . $year; ?> </a>
			</div>
		</div>
            <div class="card-body">
			<a href="<?php echo base_url('vaccine/index/' . ($month_int-1)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-angle-double-left"></i></a>
			<a href="<?php echo base_url('vaccine/index/' . ($month_int+1)); ?>" class="btn btn-outline-success btn-sm"><i class="fas fa-angle-double-right"></i></a>
			<br/>
			<br/>
			<br/>
			
			<?php if($expiring_vacs): ?>
			<table class="table" id="dataTable">
            <thead>
				<tr>
				  <th>Owner</th>
				  <th>Last Visit</th>
				  <th>Date Expire</th>
				  <th>Vaccine</th>
				  <th>Pets</th>
				  <th>Mail</th>
				  <th>Vet</th>
				  <th>Location</th>
				</tr>
			  </thead>
			  <tbody>
				<?php foreach($expiring_vacs as $vac): ?>
				<tr>
				  <td><a href="<?php echo base_url('owners/detail/' . $vac['owner_id']); ?>"><?php echo $vac['last_name']; ?></a></td>
				  <td><?php echo ($vac['last_bill']) ? time_ago($vac['last_bill']) : '-'; ?></td>
				  <td><?php echo user_format_date($vac['redo_date'],  $user->user_date); ?></td>
				  <td><?php echo $vac['product_name']; ?></td>
				  <td><?php echo $vac['pet_name']; ?></td>
				  <td>
                    <span class="fa-stack">
                        <i class="fas fa-envelope-open-text fa-stack-1x"></i>
                        <?php echo ($vac['owner_mail']) ? '<i class="fas fa-check fa-stack-1x" style="color:Green"></i>' : '<i class="fas fa-slash fa-stack-1x" style="color:Tomato"></i>' ;?>
                    </span>
                  </td>
				  <td><?php echo $vac['vet_name']; ?></td>
				  <td><?php echo $vac['location']; ?></td>
				</tr>
				<?php endforeach; ?>
			  </tbody>
			</table>
			<?php endif; ?>
			</div>
	  </div>
	</div>
</div>


<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#vaccines").addClass('active');

	$("#dataTable").DataTable({
			"pageLength": 50,
			"lengthMenu": [[50, 100, -1], [50, 100, "All"]],
			"order": [[ 2, "asc" ]],
			"responsive": true
	});
});
</script>