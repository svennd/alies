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
					<div class="card">
						<div class="card-header d-flex flex-row align-items-center justify-content-between">
							<div><?php echo $this->lang->line('product_types'); ?></div>
							<div class="dropdown no-arrow">
								<a href="#" class="btn btn-outline-success btn-sm toggle-add-form" data-target="type_add_form"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add_product_type'); ?></a>
							</div>
						</div>
						<div class="card-body">
							<div id="type_add_form" style="display:none;">
								<form method="post" action="<?php echo base_url('admin/product_types'); ?>">
										<div class="form-row">
											<div class="col-md-3 mb-2">
												<input type="text" class="form-control" name="name" value="" />
											</div>
											<div class="col-md-3 mb-2">
												<input type="text" class="form-control" name="icon" value="" placeholder="fa-solid fa-pills" />
											</div>
											<div class="col-md-2 mb-2">
												<input type="color" class="form-control" name="icon_color" value="#0d6efd" />
											</div>
											<div class="col-md-2 mb-2">
												<select name="root" class="form-control">
											<option value=""><?php echo $this->lang->line('root_category'); ?></option>
											<?php foreach ($prod_type_roots as $root): ?>
												<option value="<?php echo $root['id']; ?>"><?php echo html_escape($root['name']); ?></option>
											<?php endforeach; ?>
										</select>
											</div>
											<div class="col-md-2 mb-2">
												<button type="submit" name="submit" value="add_product_type" class="btn btn-primary btn-block"><?php echo $this->lang->line('add_product_type'); ?></button>
											</div>
										</div>
									</form>
									<small class="form-text text-muted mb-3">Use an optional Font Awesome class like <code>fa-solid fa-pills</code>. Leave parent empty for a root category.</small>
								</div>

							<?php if ($prod_type): ?>
								<?php
									$root_types = array();
									$child_types = array();
									foreach ($prod_type as $type) {
										if (is_null($type['root'])) {
											$root_types[] = $type;
											continue;
										}

										if (!isset($child_types[$type['root']])) {
											$child_types[$type['root']] = array();
										}
										$child_types[$type['root']][] = $type;
									}
								?>
								<ul class="list-unstyled mb-0 product-type-tree">
									<?php foreach ($root_types as $type): ?>
										<li class="mb-3">
											<div class="border rounded p-3 bg-light">
													<div id="type_name_<?php echo $type['id']; ?>" class="d-flex flex-row align-items-center justify-content-between">
														<div>
															<strong>
																<?php if (!empty($type['icon'])): ?><i class="<?php echo html_escape($type['icon']); ?> mr-1" <?php echo !empty($type['icon_color']) ? 'style="color: ' . html_escape($type['icon_color']) . ';"' : ''; ?>></i><?php endif; ?>
																<?php echo html_escape($type['name']); ?>
															</strong>
															<div class="text-muted small"><?php echo $this->lang->line('root_category'); ?></div>
														</div>
													<div>
														<a href="#" class="edit-item btn btn-outline-success btn-sm" data-prefix="type" data-id="<?php echo $type['id']; ?>"><i class="fas fa-edit"></i></a>
														<a href="<?php echo base_url('admin/product_types_rm/' . $type['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
													</div>
												</div>
												<div id="type_edit_<?php echo $type['id']; ?>" style="display:none;">
														<form method="post" action="<?php echo base_url('admin/product_types'); ?>">
															<div class="form-row">
																<div class="col-md-3 mb-2">
																	<input type="text" class="form-control" name="name" value="<?php echo html_escape($type['name']); ?>" />
																</div>
																<div class="col-md-3 mb-2">
																	<input type="text" class="form-control" name="icon" value="<?php echo html_escape($type['icon'] ?? ''); ?>" placeholder="fa-solid fa-pills" />
																</div>
																<div class="col-md-2 mb-2">
																	<input type="color" class="form-control" name="icon_color" value="<?php echo html_escape($type['icon_color'] ?? '#0d6efd'); ?>" />
																</div>
																<div class="col-md-2 mb-2">
																	<select name="root" class="form-control">
																		<option value=""><?php echo $this->lang->line('root_category'); ?></option>
																		<?php foreach ($prod_type_roots as $root): ?>
																		<option value="<?php echo $root['id']; ?>" <?php echo ((int) $type['root'] === (int) $root['id']) ? "selected='selected'" : ""; ?>>
																			<?php echo html_escape($root['name']); ?>
																		</option>
																	<?php endforeach; ?>
																	</select>
																</div>
																<div class="col-md-2 mb-2">
																	<input type="hidden" name="id" value="<?php echo $type['id']; ?>" />
																	<button type="submit" name="submit" value="update_product_type" class="btn btn-primary btn-block">Update</button>
																</div>
														</div>
													</form>
												</div>

												<?php if (!empty($child_types[$type['id']])): ?>
													<ul class="list-unstyled mt-3 mb-0 pl-3 border-left">
														<?php foreach ($child_types[$type['id']] as $child): ?>
															<li class="mt-2">
																	<div id="type_name_<?php echo $child['id']; ?>" class="d-flex flex-row align-items-center justify-content-between">
																		<div>
																			<span>
																				<?php if (!empty($child['icon'])): ?><i class="<?php echo html_escape($child['icon']); ?> mr-1" <?php echo !empty($child['icon_color']) ? 'style="color: ' . html_escape($child['icon_color']) . ';"' : ''; ?>></i><?php endif; ?>
																				<?php echo html_escape($child['name']); ?>
																			</span>
																			<div class="text-muted small"><?php echo html_escape($type['name']); ?> / <?php echo html_escape($child['name']); ?></div>
																		</div>
																	<div>
																		<a href="#" class="edit-item btn btn-outline-success btn-sm" data-prefix="type" data-id="<?php echo $child['id']; ?>"><i class="fas fa-edit"></i></a>
																		<a href="<?php echo base_url('admin/product_types_rm/' . $child['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
																	</div>
																</div>
																<div id="type_edit_<?php echo $child['id']; ?>" style="display:none;" class="mt-2">
																		<form method="post" action="<?php echo base_url('admin/product_types'); ?>">
																			<div class="form-row">
																				<div class="col-md-3 mb-2">
																					<input type="text" class="form-control" name="name" value="<?php echo html_escape($child['name']); ?>" />
																				</div>
																				<div class="col-md-3 mb-2">
																					<input type="text" class="form-control" name="icon" value="<?php echo html_escape($child['icon'] ?? ''); ?>" placeholder="fa-solid fa-pills" />
																				</div>
																				<div class="col-md-2 mb-2">
																					<input type="color" class="form-control" name="icon_color" value="<?php echo html_escape($child['icon_color'] ?? '#0d6efd'); ?>" />
																				</div>
																				<div class="col-md-2 mb-2">
																					<select name="root" class="form-control">
																						<option value=""><?php echo $this->lang->line('root_category'); ?></option>
																						<?php foreach ($prod_type_roots as $root): ?>
																						<option value="<?php echo $root['id']; ?>" <?php echo ((int) $child['root'] === (int) $root['id']) ? "selected='selected'" : ""; ?>>
																							<?php echo html_escape($root['name']); ?>
																						</option>
																					<?php endforeach; ?>
																					</select>
																				</div>
																				<div class="col-md-2 mb-2">
																					<input type="hidden" name="id" value="<?php echo $child['id']; ?>" />
																					<button type="submit" name="submit" value="update_product_type" class="btn btn-primary btn-block">Update</button>
																				</div>
																		</div>
																	</form>
																</div>
															</li>
														<?php endforeach; ?>
													</ul>
												<?php endif; ?>
											</div>
										</li>
									<?php endforeach; ?>
								</ul>
							<?php endif; ?>
						</div>
					</div>

					<div class="card my-4">
							<div class="card-header d-flex flex-row align-items-center justify-content-between">
								<div><?php echo $this->lang->line('product_labels'); ?></div>
								<div class="dropdown no-arrow">
									<a href="#" class="btn btn-outline-primary btn-sm toggle-add-form" data-target="label_add_form"><i class="fas fa-plus"></i> <?php echo $this->lang->line('add_product_label'); ?></a>
								</div>
							</div>
							<div class="card-body">
								<div id="label_add_form" style="display:none;">
									<form method="post" action="<?php echo base_url('admin/product_types'); ?>">
										<div class="form-row">
											<div class="col-md-9 mb-2">
												<input type="text" class="form-control" name="name" value="" />
											</div>
											<div class="col-md-3 mb-2">
												<button type="submit" name="submit" value="add_product_label" class="btn btn-primary btn-block"><?php echo $this->lang->line('add_product_label'); ?></button>
											</div>
										</div>
									</form>
								</div>

								<?php if ($prod_label): ?>
									<ul class="list-group list-group-flush">
										<?php foreach ($prod_label as $label): ?>
											<li class="list-group-item px-0">
												<div id="label_name_<?php echo $label['id']; ?>" class="d-flex flex-row align-items-center justify-content-between">
													<span><?php echo html_escape($label['name']); ?></span>
													<div>
														<a href="#" class="edit-item btn btn-outline-primary btn-sm" data-prefix="label" data-id="<?php echo $label['id']; ?>"><i class="fas fa-edit"></i></a>
														<a href="<?php echo base_url('admin/product_labels_rm/' . $label['id']); ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-trash-alt"></i></a>
													</div>
												</div>
												<div id="label_edit_<?php echo $label['id']; ?>" style="display:none;" class="mt-2">
													<form method="post" action="<?php echo base_url('admin/product_types'); ?>">
														<div class="form-row">
															<div class="col-md-9 mb-2">
																<input type="text" class="form-control" name="name" value="<?php echo html_escape($label['name']); ?>" />
															</div>
															<div class="col-md-3 mb-2">
																<input type="hidden" name="id" value="<?php echo $label['id']; ?>" />
																<button type="submit" name="submit" value="update_product_label" class="btn btn-primary btn-block">Update</button>
															</div>
														</div>
													</form>
												</div>
											</li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>
					</div>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#admin").addClass('active');

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
