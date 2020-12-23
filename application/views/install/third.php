<div class="container">
  <div class="row">
    <div class="col-sm">
		<ul class="steps">
		  <li class="step step-success">
			<div class="step-content">
			  <span class="step-circle">1</span>
			  <span class="step-text">Step 1</span>
			</div>
		  </li>
		  <li class="step step-success">
			<div class="step-content">
			  <span class="step-circle">2</span>
			  <span class="step-text">Step 2</span>
			</div>
		  </li>
		  <li class="step step-active">
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
			Step 3 : user credentials
		  </div>
		  <div class="card-body">
			<h5 class="card-title">Configuration</h5>
			<p class="card-text">
				<form action="<?php echo base_url(); ?>install/third" method="post" autocomplete="off">
					<h4>Base Config</h4>
					<div class="form-group">
						<label for="base_url">Base URL</label>
						<input type="text" class="form-control" name="base_url" value="<?php echo base_url(); ?>" id="base_url" aria-describedby="base_url_help">
						<small id="base_url_help" class="form-text text-muted">URL to your CodeIgniter root. Typically this will be your base URL, with a trailing slash. (eg: http://example.com/)</small>
					</div>
					<hr />
					<h4>Database Config</h4>
					<div class="form-group">
						<label for="hostname">HostName</label>
						<input type="text" class="form-control" name="hostname" id="hostname" placeholder="localhost" />
					</div>
					<div class="form-group">
						<label for="db_username">Database Username</label>
						<input type="text" class="form-control" name="db_username" id="db_username" placeholder="localhost" />
					</div>
					<div class="form-group">
						<label for="db_passwd">Database Password</label>
						<input type="password" class="form-control" name="db_passwd" id="db_passwd" placeholder="********" />
					</div>
					<div class="form-group">
						<label for="db_name">Database Name</label>
						<input type="text" class="form-control" name="db_name" id="db_name" placeholder="" />
					</div>
					<button type="submit" class="btn btn-primary">Submit</button>
				</form>
			</p>
		  </div>
		</div>
    </div>
  </div>
</div>