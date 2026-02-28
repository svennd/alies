<style>
.profile-tabs {
  border-bottom: 1px solid #e3e6f0;
}

.profile-tabs .nav-link {
  position: relative;
  color: #6c757d;
  padding: 0.75rem 1rem;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.profile-tabs .nav-link i {
  transition: color 0.3s ease;
}

/* Soft icon colors */
#vamreg-in i { color: #6fbf73; }          /* soft green */
#vamreg-out i { color: #f08a8a; }         /* soft red */
#vamreg-product-list i { color: #7da9f7; }/* soft blue */
#vamreg-send i { color: #9c88ff; }        /* soft purple */

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
    <a class="nav-link" href="<?php echo base_url('vamreg/index'); ?>" id="vamreg-in"><i class="fa-solid fa-right-to-bracket"></i> Vamreg IN</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo base_url('vamreg/out'); ?>" id="vamreg-out"><i class="fa-solid fa-right-from-bracket"></i> Vamreg OUT</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo base_url('vamreg/product_list'); ?>" id="vamreg-product-list"><i class="fa-solid fa-book-atlas"></i> Product list</a>
  </li>
  <li class="nav-item">
    <a class="nav-link" href="<?php echo base_url('vamreg/send'); ?>" id="vamreg-send"><i class="fa-solid fa-cloud-arrow-up"></i> Send</a>
  </li>
</ul>