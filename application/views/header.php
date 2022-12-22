<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Alies - <?php echo $this->lang->line('dashboard'); ?></title>

	<link href="<?php echo base_url(); ?>vendor/components/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/sb-admin-2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/custom.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/datatables.min.css" rel="stylesheet"> <!-- datatables -->
	<link href="<?php echo base_url(); ?>vendor/components/font-awesome/css/all.min.css" rel="stylesheet"> <!-- font awesome -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<!-- select 2 -->
	<link href="<?php echo base_url(); ?>vendor/select2/select2/dist/css/select2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/select2-bootstrap4.min.css" rel="stylesheet">
	<?php echo (isset($extra_header)) ? $extra_header : ""; ?>


	<link rel="icon" href="<?php echo base_url(); ?>assets/alies.ico" type="image/x-icon" />
</head>
<body id="page-top">

  <!-- mondal if required -->
  <?php echo $mondal; ?>

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar" style="background-image:linear-gradient(180deg, <?php echo $user->sidebar; ?> 10%, <?php echo $user->sidebar; ?> 100%);">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item">
        <a class="nav-link" href="<?php echo base_url(); ?>">
          <i class="fas fa-fw fa-tachometer-alt"></i>
          <span><?php echo $this->lang->line('dashboard'); ?></span></a>
      </li>

	  <?php if ($this->ion_auth->in_group("admin")): ?>
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
          <span>Pricing</span>
        </a>
        <div id="pricingmg" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <a class="collapse-item" href="<?php echo base_url(); ?>admin/proc" id="adminproc">Procedures</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>products/product_price" id="prod_list">Products</a>
          </div>
        </div>
      </li>

      <li class="nav-item" id="reportsmgm">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#rep" aria-expanded="true" aria-controls="rep">
          <i class="fas fa-fw fa-receipt"></i>
          <span>Reports</span>
        </a>
        <div id="rep" class="collapse" aria-labelledby="rep" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <!-- <a class="collapse-item" href="<?php echo base_url(); ?>reports/clients" id="client_report">Clients</a> -->
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/products" id="products_report">Products</a>
            <!-- <a class="collapse-item" href="<?php echo base_url(); ?>reports/stock_list" id="stock_list">Stock List</a> -->
          </div>
        </div>
      </li>
	  <?php endif; ?>
      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
      <?php echo $this->lang->line('Veterinary'); ?>
      </div>
      <li class="nav-item" id="clients">
        <a class="nav-link" href="<?php echo base_url(); ?>owners">
          <i class="fas fa-fw fa-user"></i>
          <span><?php echo $this->lang->line('Clients'); ?></span></a>
      </li>
      <li class="nav-item" id="reports">
        <a class="nav-link" href="<?php echo base_url(); ?>report">
         <i class="far fa-fw fa-file"></i>
          <span><?php echo $this->lang->line('Reports'); ?></span></a>
      </li>
      <hr class="sidebar-divider">
      <div class="sidebar-heading">
      <?php echo $this->lang->line('Administration'); ?>
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
          <span><?php echo $this->lang->line('Lab'); ?></span></a>
      </li>
      <li class="nav-item" id="product_list">
        <a class="nav-link" href="<?php echo base_url('products'); ?>">
          <i class="fas fa-fw fa-dolly"></i>
          <span><?php echo $this->lang->line('Products'); ?></span></a>
      </li>

    </ul>
    <!-- End of Sidebar -->

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Sidebar Toggle (Topbar) -->
          <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
          </button>

        <!-- Topbar Search -->
					<?php echo (isset($location)) ? $location : '<span style="color:red">location error</span>'; ?>

          <!-- Topbar Navbar -->

          <ul class="navbar-nav ml-auto">

          <?php if ($this->ion_auth->in_group("admin")): ?>
	          <li class="nav-item mx-1">
	              <a class="nav-link" href="<?php echo base_url('admin/enable_vsens'); ?>">
                <?php if($user->vsens): ?>
                  <i class="fas fa-user-shield" style="color:#f6c23e;"></i>
                <?php else: ?>
                  <i class="fas fa-user-shield" style="color:#ff5555;"></i>
                <?php endif; ?>
	              </a>
            </li>
          <?php endif; ?>
	          <li class="nav-item mx-1">
	              <a class="nav-link" href="<?php echo base_url('report'); ?>">
	                  <i class="fas fa-file fa-fw"></i>
										<?php if ($report_count > 0): ?>
	                  <span class="badge badge-info badge-counter"><?php echo $report_count; ?></span>
										<?php endif; ?>
	              </a>
	          </li>
            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
										<?php echo (isset($user)) ? $user->first_name . ' ' . $user->last_name : 'error name'; ?>
								</span>
                <img class="img-profile rounded" src="<?php echo base_url() . 'assets/public/' . (!empty($user->image) && is_readable('assets/public/' . $user->image) ? $user->image : 'unknown.jpg' ) ; ?>" />
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
				        <a class="dropdown-item" href="<?php echo base_url(); ?>vet/profile">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profile
                </a>
                <a class="dropdown-item" href="<?php echo base_url(); ?>vet/change_password">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  Settings
                </a>
				<!--
                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Activity Log
                </a>
				-->
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>
          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">
