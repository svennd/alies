<div class="row">
	<div class="col-lg-12 mb-4">
		<div class="card shadow mb-4">
			<div class="card-header d-flex flex-row align-items-center justify-content-between">
				<div><a href="<?php echo base_url('accounting/dashboard'); ?>"><?php echo $this->lang->line('admin'); ?></a> / <?php echo $this->lang->line('product_types_and_labels'); ?></div>
			</div>
			<div class="card-body">
				<p class="text-muted mb-4">
					<?php echo $this->lang->line('product_type_description'); ?><br>
					<?php echo $this->lang->line('product_label_description'); ?>
				</p>

				<div class="row">
					<div class="col-lg-6 mb-4">
						<div class="card border-left-success h-100">
							<div class="card-header d-flex flex-row align-items-center justify-content-between">
								<div><?php echo $this->lang->line('product_types'); ?></div>
								<div class="dropdown no-arrow">
									<a href="#" class="btn btn-outline-success btn-sm toggle-add-form" data-target="type_add_form"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add_product_type'); ?></a>
								</div>
							</div>
							<div class="card-body">
								<div id="type_add_form" style="display:none;">
									<form method="post" action="<?php echo base_url('admin/product_types'); ?>" class="form-inline">
										<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="" />
										<button type="submit" name="submit" value="add_product_type" class="btn btn-primary mb-2"><?php echo $this->lang->line('add_product_type'); ?></button>
									</form>
									<br/>
								</div>

								<?php if ($prod_type): ?>
									<table class="table" id="productTypesTable">
										<thead>
											<tr>
												<th>Type</th>
												<th><?php echo $this->lang->line('edit'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($prod_type as $type): ?>
												<tr>
													<td>
														<div id="type_name_<?php echo $type['id']; ?>">
															<?php echo html_escape($type['name']); ?>
														</div>
														<div id="type_edit_<?php echo $type['id']; ?>" style="display:none;">
															<form method="post" action="<?php echo base_url('admin/product_types'); ?>" class="form-inline">
																<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="<?php echo html_escape($type['name']); ?>" />
																<input type="hidden" name="id" value="<?php echo $type['id']; ?>" />
																<button type="submit" name="submit" value="update_product_type" class="btn btn-primary mb-2">Update</button>
															</form>
														</div>
													</td>
													<td>
														<a href="#" class="edit-item btn btn-outline-success btn-sm" data-prefix="type" data-id="<?php echo $type['id']; ?>"><i class="fas fa-edit"></i></a>
														&nbsp;
														<a href="<?php echo base_url('admin/product_types_rm/' . $type['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								<?php endif; ?>
							</div>
						</div>
					</div>

					<div class="col-lg-6 mb-4">
						<div class="card border-left-primary h-100">
							<div class="card-header d-flex flex-row align-items-center justify-content-between">
								<div><?php echo $this->lang->line('product_labels'); ?></div>
								<div class="dropdown no-arrow">
									<a href="#" class="btn btn-outline-primary btn-sm toggle-add-form" data-target="label_add_form"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add_product_label'); ?></a>
								</div>
							</div>
							<div class="card-body">
								<div id="label_add_form" style="display:none;">
									<form method="post" action="<?php echo base_url('admin/product_types'); ?>" class="form-inline">
										<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="" />
										<button type="submit" name="submit" value="add_product_label" class="btn btn-primary mb-2"><?php echo $this->lang->line('add_product_label'); ?></button>
									</form>
									<br/>
								</div>

								<?php if ($prod_label): ?>
									<table class="table" id="productLabelsTable">
										<thead>
											<tr>
												<th>Label</th>
												<th><?php echo $this->lang->line('edit'); ?></th>
											</tr>
										</thead>
										<tbody>
											<?php foreach ($prod_label as $label): ?>
												<tr>
													<td>
														<div id="label_name_<?php echo $label['id']; ?>">
															<?php echo html_escape($label['name']); ?>
														</div>
														<div id="label_edit_<?php echo $label['id']; ?>" style="display:none;">
															<form method="post" action="<?php echo base_url('admin/product_types'); ?>" class="form-inline">
																<input type="text" class="form-control mb-2 mr-sm-2" name="name" value="<?php echo html_escape($label['name']); ?>" />
																<input type="hidden" name="id" value="<?php echo $label['id']; ?>" />
																<button type="submit" name="submit" value="update_product_label" class="btn btn-primary mb-2">Update</button>
															</form>
														</div>
													</td>
													<td>
														<a href="#" class="edit-item btn btn-outline-primary btn-sm" data-prefix="label" data-id="<?php echo $label['id']; ?>"><i class="fas fa-edit"></i></a>
														&nbsp;
														<a href="<?php echo base_url('admin/product_labels_rm/' . $label['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
													</td>
												</tr>
											<?php endforeach; ?>
										</tbody>
									</table>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');

	if ($("#productTypesTable").length) {
		$("#productTypesTable").DataTable();
	}

	if ($("#productLabelsTable").length) {
		$("#productLabelsTable").DataTable();
	}

	$(".toggle-add-form").on('click', function(event){
		event.preventDefault();
		var target = $(this).data('target');
		$("#" + target).show();
		$(this).hide();
	});

	$(".edit-item").on('click', function(event){
		event.preventDefault();
		var prefix = $(this).data('prefix');
		var id = $(this).data('id');
		$("#" + prefix + "_name_" + id).hide();
		$("#" + prefix + "_edit_" + id).show();
	});
});
</script>
