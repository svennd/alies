<div class="row">
		<div class="col-xl-12 col-lg-12">
			<div class="card mb-4">
					<div class="card-header">
						<?php echo $this->lang->line('Reports'); ?> 
					</div>

					<div class="card-body">
						<?php if($reports): ?>
						<table class="table">
							<tr>
								<th><?php echo $this->lang->line('client'); ?></th>
								<th><?php echo $this->lang->line('title'); ?></th>
								<th><?php echo $this->lang->line('Invoice'); ?></th>
								<th><?php echo $this->lang->line('report'); ?></th>
								<th><?php echo $this->lang->line('location'); ?></th>
								<th><?php echo $this->lang->line('vet'); ?></th>
								<th><?php echo $this->lang->line('last_update'); ?></th>
							</tr>
						<?php foreach($reports as $report): ?>
								<tr>
									<td><a href="<?php echo base_url('owners/detail/' . $report['owner_id']); ?>"><?php echo $report['owner_name']; ?></a> (<small><?php echo get_symbol($report['pet_type']) ; ?> <?php echo $report['pet_name']; ?></small>)</td>
									<td><?php echo $report['title']; ?></td>
									<td>
										<?php if($report['payment'] == 0): ?>
										---
										<?php else : ?>
										<a href="<?php echo base_url('invoice/get_bill/' . $report['payment']); ?>" class="btn btn-sm <?php echo ($report['status']) ? 'btn-outline-success' : 'btn-outline-danger'; ?>"><?php echo $this->lang->line('Invoice'); ?></a>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($report['report'] == REPORT_DONE): ?>
											<a href="<?php echo base_url('invoice/get_bill/' . $report['payment']); ?>" class="btn btn-sm btn-outline-success"><?php echo $this->lang->line('finished'); ?></a>
										<?php else : ?>
											<a href="<?php echo base_url('events/event/'. $report['id']); ?>" class="btn btn-sm btn-outline-danger"><?php echo $this->lang->line('invoice_open'); ?></a>
										<?php endif; ?>
									</td>	
									<td><?php echo $report['loc_name']; ?></td>
									<td><?php echo $report['first_name']; ?></td>
									<td><?php echo user_format_date($report['updated_at'], $user->user_date); ?></td>
								</tr>
						<?php endforeach; ?>
						</table>
						<?php else: ?>
							No recent reports.
						<?php endif; ?>
					</div>
			</div>
		</div>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#reports").addClass('active');
});
</script>
