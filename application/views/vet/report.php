<div class="row">
	<?php if($reports): ?>
		<div class="col-xl-12 col-lg-12">
			<div class="card mb-4">
					<div class="card-header">
					Reports
					</div>

					<div class="card-body">
						<table class="table">
							<tr>
								<th>#</th>
								<th>Title</th>
								<th>Pet</th>
								<th>Invoice</th>
								<th>Report</th>
								<th>Location</th>
								<th>Last Update</th>
							</tr>
						<?php foreach($reports as $report): ?>
								<tr>
									<td><a href="<?php echo base_url('events/event/'. $report['id']); ?>" class="btn btn-sm btn-outline-info">consult</a></td>
									<td><?php echo $report['title']; ?></td>
									<td><a href="<?php echo base_url('pets/fiche/'. $report['pet']['id']); ?>"><?php echo get_symbol($report['pet']['type']) ; ?> <?php echo $report['pet']['name']; ?></a></td>
									<td><a href="<?php echo base_url('invoice/get_bill/' . $report['payment']); ?>" class="badge <?php echo ($report['status']) ? 'badge-success' : 'badge-danger'; ?>">invoice</a></td>
									<td>
										<?php if ($report['report'] == REPORT_DONE): ?>
											<a href="<?php echo base_url('invoice/get_bill/' . $report['payment']); ?>" class="badge badge-success">finished</a>
										<?php else : ?>
											<a href="<?php echo base_url('events/event/'. $report['id']); ?>" class="badge badge-danger">open</a>
										<?php endif; ?>
									</td>
									<td><?php echo $report['location']['name']; ?></td>
									<td><?php echo user_format_date($report['updated_at'], $user->user_date); ?></td>
								</tr>
						<?php endforeach; ?>
						</table>
					</div>
			</div>
		</div>
	<?php endif; ?>
</div>
