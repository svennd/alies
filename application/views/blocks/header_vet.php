      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
      <?php echo $this->lang->line('Veterinary'); ?>
      </div>
      <!-- <li class="nav-item" id="clients">
        <a class="nav-link" href="<?php echo base_url('search'); ?>">
          <i class="fas fa-fw fa-user"></i>
          <span><?php echo $this->lang->line('Clients'); ?></span></a>
      </li> -->
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
      <li class="nav-item" id="sticky">
        <a class="nav-link" href="<?php echo base_url('sticky'); ?>">
        <i class="far fa-fw fa-sticky-note"></i>
        <span><?php echo $this->lang->line('sticky'); ?></span>
    
        <?php if ($cnt_sticky > 0): ?>
            <span class="counter" style="background-color: #72a751cb;"><?php echo $cnt_sticky; ?></span>
        <?php endif; ?>
        </a>
      </li>
      <hr class="sidebar-divider">
      <div class="sidebar-heading">
      
	    <?php if ($this->ion_auth->in_group("admin")): ?><?php echo $this->lang->line('vet'); ?> <?php endif; ?><?php echo $this->lang->line('Administration'); ?>
      </div>
      <li class="nav-item" id="invoice">
        <a class="nav-link" href="<?php echo base_url(); ?>invoice">
          <i class="fas fa-fw fa-euro-sign"></i>
          <span><?php echo $this->lang->line('Invoice'); ?></span></a>
      </li>
      <li class="nav-item" id="vaccines">
        <a class="nav-link" href="<?php echo base_url(); ?>vaccine">
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
      <li class="nav-item" id="product_list">
        <a class="nav-link" href="<?php echo base_url('products'); ?>">
          <i class="fas fa-fw fa-dolly"></i>
          <span><?php echo $this->lang->line('Products'); ?></span></a>
      </li>

      <?php if (!$this->ion_auth->in_group("admin")): ?>
      <li class="nav-item" id="help">
      <a class="nav-link" href="<?php echo base_url('help/vet'); ?>">
          <i class="fa-regular fa-fw fa-circle-question"></i>
          <span>Help</span></a>
      </li> 
      <?php endif; ?>