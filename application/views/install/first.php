<div class="container">
  <div class="row">
    <div class="col-sm">
		<ul class="steps">
		  <li class="step step-active">
			<div class="step-content">
			  <span class="step-circle">1</span>
			  <span class="step-text">Step 1</span>
			</div>
		  </li>
		  <li class="step">
			<div class="step-content">
			  <span class="step-circle">2</span>
			  <span class="step-text">Step 2</span>
			</div>
		  </li>
		  <li class="step">
			<div class="step-content">
			  <span class="step-circle">3</span>
			  <span class="step-text">Step 3</span>
			</div>
		  </li>
		  <li class="step">
			<div class="step-content">
			  <span class="step-circle">4</span>
			  <span class="step-text">Step 4</span>
			</div>
		  </li>
		</ul>
		<br/>
		<div class="card">
		  <div class="card-header">
			Step 1 : Initial setup
		  </div>
		  <div class="card-body">
			<h5 class="card-title">Welcome to the Alies installer !</h5>
			<p class="card-text">Lets go and install Alies, you will need : 
				<ul>
					<li>MySQL / MariaDB credentials : username, password, database</li>
					<li>Domain or IP adress of the server</li>
					<li>Credentials of the first user</li>
				</ul>
			</p>
			<?php if($writable): ?>
				<div class="alert alert-warning" role="alert">
				  A simple danger alert—check it out!
				</div>
			<?php endif; ?>
			<a href="<?php echo base_url() . 'install/second'; ?>" class="btn btn-primary">Ok, Let's go !</a>
		  </div>
		</div>
    </div>
  </div>
</div>