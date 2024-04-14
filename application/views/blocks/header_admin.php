<!-- Divider -->
<hr class="sidebar-divider">

<div class="sidebar-heading"><?php echo $this->lang->line('Administration'); ?></div>

<li class="nav-item" id="admin">
<a class="nav-link" href="<?php echo base_url('accounting/dashboard'); ?>">
    <i class="fas fa-fw fa-user-shield"></i>
    <span>Admin</span></a>
</li>  

<li class="nav-item" id="sticky">
    <a class="nav-link" href="<?php echo base_url('sticky'); ?>">
    <i class="far fa-fw fa-sticky-note"></i>
    <span><?php echo $this->lang->line('sticky'); ?></span>

    <?php if ($cnt_sticky > 0): ?>
        <span class="counter" style="background-color: #72a751cb;"><?php echo $cnt_sticky; ?></span>
    <?php endif; ?>
    </a>
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
    <span><?php echo $this->lang->line('registers'); ?></span>
</a>
<div id="rep" class="collapse" aria-labelledby="rep" data-parent="#accordionSidebar">
    <div class="bg-white py-2 collapse-inner rounded">
    <a class="collapse-item" href="<?php echo base_url('reports/register_in'); ?>" id="reg_in"><?php echo $this->lang->line('Reg_in'); ?></a>
    <a class="collapse-item" href="<?php echo base_url('reports/register_out'); ?>" id="reg_out"><?php echo $this->lang->line('Reg_out'); ?></a>
    </div>
</div>
</li>

<li class="nav-item" id="invoice">
    <a class="nav-link" href="<?php echo base_url('invoice'); ?>">
        <i class="fas fa-fw fa-euro-sign"></i>
        <span><?php echo $this->lang->line('Invoice'); ?></span></a>
</li>

<!-- Vet -->
<hr class="sidebar-divider">

<div class="sidebar-heading"><?php echo $this->lang->line('Stock'); ?></div>

<li class="nav-item" id="product_list">
        <a class="nav-link" href="<?php echo base_url('products'); ?>">
          <i class="fas fa-fw fa-dolly"></i>
          <span><?php echo $this->lang->line('Products'); ?></span></a>
      </li>

<li class="nav-item" id="add_stock">
    <a class="nav-link" href="<?php echo base_url('stock/add_stock'); ?>">
    <i class="fa-solid fa-cart-plus"></i>
    <span><?php echo $this->lang->line('add'); ?></span></a>
</li>

<li class="nav-item" id="move_stock">
    <a class="nav-link" href="<?php echo base_url('stock/move'); ?>">
    <i class="fa-solid fa-truck-fast"></i>
    <span><?php echo $this->lang->line('move'); ?></span></a>
</li>

<hr class="sidebar-divider">
<div class="sidebar-heading"><?php echo $this->lang->line('vet'); ?></div>

<li class="nav-item" id="reports">
        <a class="nav-link" href="<?php echo base_url(); ?>report">
        <i class="fa-solid fa-fw fa-book-medical"></i>
        <?php if ($report_count > 0): ?>
            <span class="counter" style="background-color: #e7493bcb;"><?php echo $report_count; ?></span>
        <?php else: ?>
            <span class="counter"><i class="fa-solid fa-check" style="color:#c2efa7;"></i></span>
        <?php endif; ?>
        <span><?php echo $this->lang->line('Reports'); ?></span></a>
      </li>

<li class="nav-item" id="vaccines">
    <a class="nav-link" href="<?php echo base_url('vaccine'); ?>">
    <i class="fas fa-syringe fa-fw"></i>
        <span><?php echo $this->lang->line('Vaccins'); ?></span></a>
</li>

<li class="nav-item" id="labo">
    <a class="nav-link" href="<?php echo base_url('lab'); ?>">
    <i class="fas fa-flask fa-fw"></i>
        <span><?php echo $this->lang->line('Lab'); ?></span>
        <?php if ($lab_count > 0): ?>
        <span class="counter" style="background-color: #72a751cb;"><?php echo $lab_count; ?></span>
        <?php endif; ?>
    </a>
</li>

<li class="nav-item" id="help">
<a class="nav-link" href="<?php echo base_url('help/admin'); ?>">
    <i class="fa-regular fa-fw fa-circle-question"></i>
    <span>Help</span></a>
</li>
