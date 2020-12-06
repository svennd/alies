<!DOCTYPE html>
<html lang="en">

<head>

	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<meta name="description" content="">
	<meta name="author" content="">

	<title>Alies - Dashboard</title>

	<link href="<?php echo base_url(); ?>assets/css/sb-admin-2.min.css" rel="stylesheet">
	<link href="<?php echo base_url(); ?>assets/css/dataTables.bootstrap4.min.css" rel="stylesheet"> <!-- datatables -->
	<link href="<?php echo base_url(); ?>assets/css/all.min.css" rel="stylesheet"> <!-- font awesome -->
	<link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">

	<!-- select 2 -->
	<link href="<?php echo base_url(); ?>assets/css/select2.min.css" rel="stylesheet">
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

      <!-- Divider -->
      <hr class="sidebar-divider">

	  
	  <?php if ($this->ion_auth->in_group("admin")): ?>
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
	  <?php endif; ?>

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
	  
	  <?php if ($this->ion_auth->in_group("admin")): ?>
      <li class="nav-item" id="reports">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#rep" aria-expanded="true" aria-controls="rep">
          <i class="fas fa-fw fa-receipt"></i>
          <span>Reports</span>
        </a>
        <div id="rep" class="collapse" aria-labelledby="rep" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Reports :</h6>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/graphs" id="charts_report">Charts</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/bills" id="invoice_report">Invoices</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/clients" id="client_report">Clients</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/products" id="products_report">Products</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>reports/vaccine" id="vaccine_report">Vaccine</a>
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
            <h6 class="collapse-header">Backup & Restore :</h6>
            <a class="collapse-item" href="<?php echo base_url(); ?>backup" id="backup">Backup</a>
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
        <a class="nav-link collapsed" href="<?php echo base_url(); ?>owners" data-toggle="collapse" data-target="#owners" aria-expanded="true" aria-controls="owners">
          <i class="fas fa-fw fa-user"></i>
          <span>Clients</span></a>
		  
		  <div id="owners" class="collapse" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
          <div class="bg-white py-2 collapse-inner rounded">
            <h6 class="collapse-header">Clients :</h6>
            <a class="collapse-item" href="<?php echo base_url(); ?>owners/search" id="client_search">Client Search</a>
            <a class="collapse-item" href="<?php echo base_url(); ?>owners/add" id="new_client">New Client</a>
          </div>
      </li>

      <li class="nav-item" id="pets">
        <a class="nav-link" href="<?php echo base_url(); ?>pets">
          <i class="fas fa-fw fa-dog"></i>
          <span>Pets</span></a>
      </li>
      <li class="nav-item" id="invoice">
        <a class="nav-link" href="<?php echo base_url(); ?>invoice">
          <i class="fas fa-file-invoice-dollar"></i>
          <span>Invoice</span></a>
      </li>

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
      <!-- Divider -->
      <hr class="sidebar-divider d-none d-md-block">

      <!-- Sidebar Toggler (Sidebar) -->
      <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
      </div>

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


			<!--
			<?php if ($this->ion_auth->in_group("admin")): ?>

            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
				<?php $unread = 0; ?>
				<?php if(isset($alerts)) : ?>
					<?php if($alerts) : ?>
						<?php foreach($alerts as $a): ?>
							<?php if ($a['user_accepted'] == 0):  ?>
								<?php $unread++; ?>
							<?php endif; ?>
						<?php endforeach; ?>
						<?php if ($unread > 0): ?>
							<span class="badge badge-danger badge-counter"><?php echo $unread; ?></span>
						<?php endif; ?>
					<?php endif; ?>
				<?php endif; ?>
              </a>
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                  Alerts Center
                </h6>
				<?php if(isset($alerts) && $alerts) : ?>
				<?php foreach ($alerts as $alert): ?>
					<a class="dropdown-item d-flex align-items-center" href="#">
					  <div class="mr-3">
						<div class="icon-circle bg-warning">
						  <i class="fas fa-exclamation-triangle text-white"></i>
						</div>
					  </div>
					  <div>
						<div class="small text-gray-500"><?php echo timespan(strtotime($alert['created_at']), time(), 1); ?></div>
						<span class="font-weight-bold"><?php echo $alert['msg']; ?></span>
					  </div>
					</a>
				<?php endforeach; ?>
				<?php endif; ?>
                <a class="dropdown-item text-center small text-gray-500" href="#">Show All Alerts</a>
              </div>
            </li>
			<?php endif; ?>
			-->
			<!--
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
				<?php $messages = (isset($messages)) ? $messages : 0; ?>
				<?php if ($messages > 0):  ?>
				
				<?php $unread = 0; ?>
				<?php foreach($messages as $message): ?>
					<?php if ($message['status'] == MSG_UNREAD):  ?>
						<?php $unread++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
				<?php if($unread > 0): ?>
					<span class="badge badge-danger badge-counter"><?php echo $unread; ?></span>
				<?php endif; ?>
				<?php endif; ?>
              </a>
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header">
                  Message Center
                </h6>
				<?php if ($messages > 0):  ?>
				<?php foreach($messages as $message): ?>
                <a class="dropdown-item d-flex align-items-center" href="<?php echo base_url(); ?>messages/<?php echo $message['msg_id']; ?>">
                  <div class="dropdown-list-image mr-3">
                    <img class="rounded-circle" src="<?php echo base_url(); ?>assets/img/temp/alies.jpeg" alt="">
					<?php if ($message['status'] == MSG_UNREAD):  ?>
						<div class="status-indicator bg-success"></div>
					<?php elseif ($message['status'] == MSG_READ):  ?>
						<div class="status-indicator bg-primary"></div>
					<?php endif; ?>
				</div>
                  <div class="font-weight-bold">
                    <div class="text-truncate"><?php echo $message['body']; ?></div>
                    <div class="small text-gray-500"><?php echo $message['first_name'] . " " . $message['last_name']; ?> · <?php echo timespan(strtotime($message['created_at']), time(), 1); ?></div>
                  </div>
                </a>
				<?php endforeach; ?>
				<?php else : ?>
					No Messages yet.
				<?php endif; ?>
                <a class="dropdown-item text-center small text-gray-500" href="#">Read More Messages</a>
              </div>
            </li>
			--> 
			
            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
				<?php if (isset($user)): ?>
					<?php echo $user->first_name; ?> <?php echo $user->last_name; ?>
				<?php else : ?>
					<span style="color:red;">error name</span>
				<?php endif; ?>
					</span>
                <img class="img-profile rounded-circle" src="<?php echo base_url() . 'assets/public/' . (!empty($user->image) ? $user->image : 'unknown.jpg' ) ; ?>" />
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