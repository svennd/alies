<style>
.profile-tabs {
  border-bottom: 1px solid #e3e6f0;
}

.profile-tabs .nav-link {
  position: relative;
  color: #6c757d;
  padding: 0.75rem 1rem;
  transition: color 0.3s ease;
}

.profile-tabs .nav-link::after {
  content: '';
  position: absolute;
  left: 0;
  bottom: 0;
  width: 0;
  height: 2px;
  background: #4e73df;
  transition: width 0.3s ease;
}

.profile-tabs .nav-link:hover {
  color: #4e73df;
}

.profile-tabs .nav-link:hover::after {
  width: 100%;
}

.profile-tabs .nav-link.active {
  color: #4e73df;
}

.profile-tabs .nav-link.active::after {
  width: 100%;
}
</style>


<ul class="nav mb-4 profile-tabs">
  <li class="nav-item">
    <a class="nav-link" href="<?php echo base_url('vamreg/index'); ?>" id="vamreg-in">Vamreg IN</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo base_url('vamreg/out'); ?>" id="vamreg-out">Vamreg OUT</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo base_url('vamreg/product_list'); ?>" id="vamreg-product-list">Product list</a>
  </li>
</ul>