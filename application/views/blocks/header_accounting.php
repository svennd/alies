<div class="sidebar-heading">
<?php echo $this->lang->line('pricing'); ?>
</div>
<li class="nav-item" id="admin">
<a class="nav-link" href="<?php echo base_url('pricing/proc'); ?>" id="adminproc">
<i class="fa-solid fa-user-doctor"></i>
<span><?php echo $this->lang->line('procedures'); ?></span></a>
</li>  
<li class="nav-item" id="admin">
<a class="nav-link" href="<?php echo base_url('pricing/prod'); ?>" id="prod_list">
<i class="fa-solid fa-cart-shopping"></i>
<span><?php echo $this->lang->line('products'); ?></span></a>
</li>