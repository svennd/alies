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
	<link href="<?php echo base_url(); ?>vendor/fortawesome/font-awesome/css/all.min.css" rel="stylesheet"> <!-- font awesome -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<!-- select 2 -->
	<link href="<?php echo base_url(); ?>vendor/select2/select2/dist/css/select2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/select2-bootstrap4.min.css" rel="stylesheet">

  <!-- sweetalert2 -->
  <link href="<?php echo base_url(); ?>assets/css/sweetalert2.min.css" rel="stylesheet">
	<?php echo (isset($extra_header)) ? $extra_header : ""; ?>


  <?php if ($this->ion_auth->in_group("accounting")): ?>
  <link href="<?php echo base_url(); ?>assets/css/fck_accounting.css" rel="stylesheet">
  <?php endif; ?>

	<link rel="icon" href="<?php echo base_url(); ?>assets/alies.ico" type="image/x-icon" />
</head>
<body id="page-top">

  <!-- mondal if required -->
  <?php echo ($this->ion_auth->in_group("accounting")) ? '' : $mondal; ?>

  <!-- Page Wrapper -->
  <div id="wrapper" >

    <!-- Sidebar -->
    <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar" style="background-image:linear-gradient(180deg, <?php echo $user->sidebar; ?> 10%, <?php echo $user->sidebar; ?> 100%);">

      <!-- Nav Item - Dashboard -->
      <li class="nav-item">
        <a class="nav-link bounceit" href="<?php echo base_url(); ?>">
        <i class="fa-solid fa-fw fa-paw"></i>
          <span><?php echo $this->lang->line('dashboard'); ?></span></a>
      </li>

	  <?php if ($this->ion_auth->in_group("admin")): ?>
      <?php include 'blocks/header_admin.php'; ?>
	  <?php endif; ?>

    <?php if($this->ion_auth->in_group("accounting")): ?>
      <?php include 'blocks/header_accounting.php'; ?>
    <?php endif; ?>

    <?php if (!$this->ion_auth->in_group("accounting")): ?>
      <?php include 'blocks/header_vet.php'  ?>
    <?php endif; ?>
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
					<?php echo (isset($location)) ? $location : ''; ?>

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

          <?php if(!$this->ion_auth->in_group("accounting")): ?>
            <li class="nav-item" id="sticky_messages">
                <a class="nav-link " href="#">
                    <i style="color:#f6c23e;" class="far fa-sticky-note"></i>
                </a>
            </li>
            <?php endif; ?>
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
                  <?php echo $this->lang->line('profile'); ?>
                </a>
                <a class="dropdown-item" href="<?php echo base_url(); ?>vet/change_password">
                  <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                  <?php echo $this->lang->line('settings'); ?>
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  <?php echo $this->lang->line('logout'); ?>
                </a>
              </div>
            </li>
          </ul>

        </nav>

        <!-- Begin Page Content -->
        <div class="container-fluid">

