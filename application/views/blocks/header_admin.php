<!-- Divider -->
<hr class="sidebar-divider">

<!-- Heading -->
<div class="sidebar-heading">
Administration
</div>
<li class="nav-item" id="admin">
<a class="nav-link" href="<?php echo base_url('accounting/dashboard'); ?>">
    <i class="fas fa-fw fa-user-shield"></i>
    <span>Admin</span></a>
</li>      
<li class="nav-item" id="pricing">
<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#pricingmg" aria-expanded="true" aria-controls="pricingmg">
    <i class="fas fa-fw fa-dollar-sign"></i>
    <span><?php echo $this->lang->line('pricing'); ?></span>
</a>
<div id="pricingmg" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
    <a class="collapse-item" href="<?php echo base_url(); ?>pricing/proc" id="adminproc"><?php echo $this->lang->line('procedures'); ?></a>
    <a class="collapse-item" href="<?php echo base_url(); ?>pricing/prod" id="prod_list"><?php echo $this->lang->line('products'); ?></a>
    </div>
</div>
</li>

<li class="nav-item" id="reportsmgm">
<a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#rep" aria-expanded="true" aria-controls="rep">
    <i class="fas fa-fw fa-receipt"></i>
    <span><?php echo $this->lang->line('Reporting'); ?></span>
</a>
<div id="rep" class="collapse" aria-labelledby="rep" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
    <!-- <a class="collapse-item" href="<?php echo base_url(); ?>reports/clients" id="client_report">Clients</a> -->
    <a class="collapse-item" href="<?php echo base_url(); ?>reports/products" id="products_report">Products</a>
    <a class="collapse-item" href="<?php echo base_url(); ?>reports/register_in" id="reg_in"><?php echo $this->lang->line('Reg_in'); ?></a>
    <a class="collapse-item" href="<?php echo base_url(); ?>reports/register_out" id="reg_out"><?php echo $this->lang->line('Reg_out'); ?></a>
    </div>
</div>
</li>


<!-- <a class="collapse-item" href="<?php echo base_url(); ?>admin/proc" id="adminproc"><?php echo $this->lang->line('procedures'); ?></a>
<a class="collapse-item" href="<?php echo base_url(); ?>products/product_price" id="prod_list"><?php echo $this->lang->line('products'); ?></a> -->