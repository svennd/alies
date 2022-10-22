<div class="row">
	<?php if($reports): ?>
		<div class="col-xl-12 col-lg-12">
			<div class="card mb-4">
					<div class="card-header">
					<?php echo $this->lang->line('Reports'); ?>
					</div>

					<div class="card-body">
						<table class="table">
							<tr>
								<th>#</th>
								<th><?php echo $this->lang->line('title'); ?></th>
								<th><?php echo $this->lang->line('pet_info'); ?></th>
								<th><?php echo $this->lang->line('Invoice'); ?></th>
								<th><?php echo $this->lang->line('report'); ?></th>
								<th><?php echo $this->lang->line('location'); ?></th>
								<?php if (($this->ion_auth->in_group("admin"))): ?>
								<th>Vet</th>
								<?php endif; ?>
								<th><?php echo $this->lang->line('last_update'); ?></th>
							</tr>
						<?php foreach($reports as $report): ?>
								<tr>
									<td><a href="<?php echo base_url('events/event/'. $report['id']); ?>" class="btn btn-sm btn-outline-info"><?php echo $this->lang->line('consult'); ?></a></td>
									<td><?php echo $report['title']; ?></td>
									<td><a href="<?php echo base_url('pets/fiche/'. $report['pet']['id']); ?>"><?php echo get_symbol($report['pet']['type']) ; ?> <?php echo $report['pet']['name']; ?></a></td>
									<td>
										<?php if($report['payment'] == 0): ?>
										<a href="#" class="badge <?php echo ($report['status']) ? 'badge-success' : 'badge-danger'; ?>"><?php echo $this->lang->line('Invoice'); ?></a>
										<?php else : ?>
										<a href="<?php echo base_url('invoice/get_bill/' . $report['payment']); ?>" class="badge <?php echo ($report['status']) ? 'badge-success' : 'badge-danger'; ?>"><?php echo $this->lang->line('Invoice'); ?></a>
										<?php endif; ?>
									</td>
									<td>
										<?php if ($report['report'] == REPORT_DONE): ?>
											<a href="<?php echo base_url('invoice/get_bill/' . $report['payment']); ?>" class="badge badge-success"><?php echo $this->lang->line('finished'); ?></a>
										<?php else : ?>
											<a href="<?php echo base_url('events/event/'. $report['id']); ?>" class="badge badge-danger"><?php echo $this->lang->line('invoice_open'); ?></a>
										<?php endif; ?>
									</td>	
									<td><?php echo $report['location']['name']; ?></td>
									<?php if (($this->ion_auth->in_group("admin"))): ?>
									<td><?php echo $report['vet']['first_name']; ?></td>
									<?php endif; ?>
									<td><?php echo user_format_date($report['updated_at'], $user->user_date); ?></td>
								</tr>
						<?php endforeach; ?>
						</table>
					</div>
			</div>
		</div>
	<?php endif; ?>
</div>
<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#reports").addClass('active');
});
</script>
