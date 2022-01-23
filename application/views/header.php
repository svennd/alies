<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Alies - Dashboard</title>

	<link href="<?php echo base_url(); ?>vendor/components/bootstrap/css/bootstrap.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/sb-admin-2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/custom.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>vendor/datatables/datatables/media/css/dataTables.bootstrap4.min.css" rel="stylesheet"> <!-- datatables -->
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
          <span>Dashboard</span></a>
      </li>

	  <?php if ($this->ion_auth->in_group("admin")): ?>
      <!-- Divider -->
      <hr class="sidebar-divider">


      <!-- Heading -->
      <div class="sidebar-heading">
        Administration
      </div>

      <li class="nav-item" id="admin">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#adminmgm" aria-expanded="true" aria-controls="adminmgm">
          <i class="fas fa-user-shield"></i>
          <span>Admin</span>
        </a>
        <div id="adminmgm" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
		  <!--
            <a class="collapse-item" href="<?php echo base_url(); ?>admin" id="adminstat">Stats</a>
			-->
            <a class="collapse-item" href="<?php echo base_url(); ?>admin/booking" id="adminbooking">Booking codes</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>admin/breeds" id="adminbreed">Breeds</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>admin/locations" id="adminlocation">Locations</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>admin/proc" id="adminproc">Procedures</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>admin/product_types" id="product_types">Product types</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>logs" id="logs">Logs</a>
			<!--
            <a class="collapse-item" href="<?php echo base_url(); ?>alerts" id="alerts">Alerts</a>
			-->
          </div>
        </div>
      </li>

      <li class="nav-item" id="usermgm">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#usermanagement" aria-expanded="true" aria-controls="usermanagement">
          <i class="fas fa-fw fa-users"></i>
          <span>User Management</span>
        </a>
        <div id="usermanagement" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">User management :</h6>
            <a class="collapse-item" href="<?php echo base_url(); ?>member" id="userlist">User List</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>member/create_user" id="createuser">Create User</a>
			<!--
            <a class="collapse-item" href="<?php echo base_url(); ?>auth/create_group" id="creategroup">Create group</a>
			-->
          </div>
        </div>
      </li>

      <div class="sidebar-heading">
        Products
      </div>

      <li class="nav-item" id="products">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#prd" aria-expanded="true" aria-controls="prd">
          <i class="fas fa-fw fa-shopping-cart"></i>
          <span>Inventory</span>
        </a>
        <div id="prd" class="collapse" aria-labelledby="prd" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Inventory management :</h6>

            <a class="collapse-item" href="<?php echo base_url(); ?>products" id="product_list">Products</a>


            <a class="collapse-item" href="<?php echo base_url(); ?>stock" id="stock">Stock</a>
          </div>
        </div>
      </li>

      <li class="nav-item" id="reports">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#rep" aria-expanded="true" aria-controls="rep">
          <i class="fas fa-fw fa-receipt"></i>
          <span>Reports</span>
        </a>
        <div id="rep" class="collapse" aria-labelledby="rep" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
           <!-- <a class="collapse-item" href="<?php echo base_url(); ?>reports/graphs" id="charts_report">Charts</a> -->
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/bills" id="invoice_report">Invoices</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/clients" id="client_report">Clients</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/products" id="products_report">Products</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/vaccine" id="vaccine_report">Vaccine</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/stock_list" id="stock_list">Stock List</a>
          </div>
        </div>
      </li>

      <li class="nav-item" id="backup">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#res" aria-expanded="true" aria-controls="res">
          <i class="fas fa-fw fa-history"></i>
          <span>Backup & Restore</span>
        </a>
        <div id="res" class="collapse" aria-labelledby="res" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Backup :</h6>
            <a class="collapse-item" href="<?php echo base_url(); ?>backup" id="backup">Backup</a>

            <h6 class="collapse-header">Restore :</h6>
            <a class="collapse-item" href="<?php echo base_url(); ?>restore/booking" id="restore_book">Restore Booking</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>restore/locations" id="restore_loc">Restore Locations</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>restore/procedures" id="restore_proc">Restore Procedures</a>
          </div>
        </div>
      </li>
	  <?php endif; ?>

      <!-- Divider -->
      <hr class="sidebar-divider">

      <!-- Heading -->
      <div class="sidebar-heading">
        Veterinary
      </div>
      <li class="nav-item" id="clients">
        <a class="nav-link" href="<?php echo base_url(); ?>owners">
          <i class="fas fa-fw fa-user"></i>
          <span>Clients</span></a>
      </li>
      <li class="nav-item" id="invoice">
        <a class="nav-link" href="<?php echo base_url(); ?>invoice">
          <i class="fas fa-fw fa-euro-sign"></i>
          <span>Invoice</span></a>
      </li>
	  <?php if (!$this->ion_auth->in_group("admin")): ?>
      <li class="nav-item" id="product_list">
        <a class="nav-link" href="<?php echo base_url(); ?>products">
         <i class="fas fa-fw fa-shopping-cart"></i>
          <span>Products</span></a>
      </li>
      <li class="nav-item" id="stock">
        <a class="nav-link" href="<?php echo base_url(); ?>stock">
         <i class="fas fa-fw fa-dolly"></i>
          <span>Stock</span></a>
      </li>
	  <?php endif; ?>
		<!--
      <hr class="sidebar-divider">

      <div class="sidebar-heading">
        Maintenance
      </div>
      <li class="nav-item" id="games">
        <a class="nav-link" href="<?php echo base_url(); ?>games">
         <i class="fas fa-gamepad"></i>
          <span>Games</span></a>
      </li>
	  -->
		<!--
      <hr class="sidebar-divider d-none d-md-block">

      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>
		-->
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

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
										<?php echo (isset($user)) ? $user->first_name. '' . $user->last_name : 'error name'; ?>
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
