<div class="row">
	<div class="col-lg-12 mb-4">
      <div class="card shadow mb-4">
		<div class="card-header d-flex flex-row align-items-center justify-content-between"><div>Vaccines</div></div>
            <div class="card-body">
                <form method="post" action="<?php echo base_url('vaccine/export/' . $month_int); ?>" autocomplete="integrations">

                <div class="list-group mb-5 shadow">
                    <div class="list-group-item list-group-item-action nobot">
                        <div class="row align-items-center">
                            <div class="col">
                                <strong class="mb-0"><?php echo ucfirst($this->lang->line('date_month')); ?></strong>
                            </div>
                            <div class="col">
                                <input type="text" disabled="disabled" class="form-control" id="month" name="month" value="<?php echo $month; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item list-group-item-action nobot">
                        <div class="row align-items-center">
                            <div class="col align-self-baseline">
                                <strong class="mb-0">Exclude</strong>
                                <p class="text-muted mb-0"><?php echo $this->lang->line('exclude_explain'); ?></p>
                            </div>
                            <div class="col">
                                <select name="excluded_products[]" multiple class="custom-select" size="<?php echo (count($excluded_products) > 15) ? '15': count($excluded_products); ?>">
                                    <?php foreach ($excluded_products as $product): ?>
                                        <option value="<?php echo $product['product_id']; ?>"><?php echo ucfirst($product['product_name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <small><i>mutli select : ctrl + left click.</i></small>
                            </div>
                        </div>
                    </div>

                    <div class="list-group-item list-group-item-action nobot">
                        <div class="row align-items-center">
                            <div class="col align-self-baseline">
                                <strong class="mb-0"><?php echo $this->lang->line('default_date_format'); ?></strong>
                            </div>
                            <div class="col">
                                <select name="date_format" class="custom-select">
                                    <option value="%d %B %Y" selected><?php echo strftime('%d %B %Y'); ?></option>
                                    <option value="%d %B"><?php echo date('d F'); ?></option>
                                    <option value="%d-%m-%Y"><?php echo strftime('%d-%m-%Y'); ?></option>
                                    <option value="%Y-%m-%d"><?php echo strftime('%Y-%m-%d'); ?></option>
                                    <option value="%d/%m"><?php echo strftime('%d/%m'); ?></option>
                                    <option value="%A %e %B %Y"><?php echo strftime('%A %e %B %Y'); ?></option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <a href="<?php echo base_url('vaccine/index/' . $month_int); ?>" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> return</a>
                <button type="submit" name="submit" value="edit" class="btn btn-outline-success float-right"><i class="fa-solid fa-download"></i> Export</button>
                </form>
			</div>
	  </div>
	</div>
</div>

<script type="text/javascript">
document.addEventListener("DOMContentLoaded", function(){
	$("#vaccines").addClass('active');
});
</script>